<?php
namespace App\Shop\Action;

use App\Shop\Entity\Product;
use App\Shop\Table\ProductTable;
use App\Shop\Upload\PdfUpload;
use App\Shop\Upload\ProductImageUpload;
use Virton\Actions\CrudAction;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use Virton\Session\FlashHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductCrudAction extends CrudAction
{
    /**
     * @inheritDoc
     */
    protected $viewPath =  '@shop/admin/products';
    /**
     * @inheritDoc
     */
    protected $routePrefix = 'shop.admin.products';
    /**
     * @inheritDoc
     */
    protected $acceptedParams = ['name', 'slug', 'price', 'created_at', 'description'];
    /**
     * @var ProductImageUpload
     */
    private $imageUpload;
    /**
     * @var PdfUpload
     */
    private $pdfUpload;

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        ProductTable $table,
        FlashHandler $flash,
        ProductImageUpload $imageUpload,
        PdfUpload $pdfUpload
    ) {
        parent::__construct($renderer, $router, $table, $flash);
        $this->imageUpload = $imageUpload;
        $this->pdfUpload = $pdfUpload;
    }

    protected function getNewEntity()
    {
        /** @var Product $entity */
        $entity = parent::getNewEntity();
        $entity->setCreatedAt(new \DateTime());
        return $entity;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Product $item
     * @return array
     */
    protected function prePersist(ServerRequestInterface $request, $item): array
    {
        $params = array_merge($request->getParsedBody(), $request->getUploadedFiles());
        $image = $this->imageUpload->upload($params['image'], $item->getImage());
        if ($image) {
            $params['image'] = $image;
            $this->acceptedParams[] = 'image';
        }
        return array_filter($params, function ($key) {
            return in_array($key, $this->acceptedParams);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param ServerRequestInterface $request
     * @param Product $item
     */
    protected function postPersist(ServerRequestInterface $request, $item): void
    {
        $file = $request->getUploadedFiles()['pdf'];
        $productId = $item->getId() ?: $this->table->getPdo()->lastInsertId();
        $this->pdfUpload->upload($file, "$productId.pdf", "$productId.pdf");
    }

    protected function delete(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Product $product */
        $product = $this->table->find($request->getAttribute('id'));
        $this->imageUpload->delete($product->getImage());
        $this->pdfUpload->delete($product->getPdf());
        return parent::delete($request);
    }

    protected function getValidator(ServerRequestInterface $request)
    {
        $validator = parent::getValidator($request)
            ->extension('pdf', ['pdf'])
            ->extension('image', ['jpg', 'png'])
            ->dateTime('created_at')
            ->numeric('price')
            ->length('description', 5)
            ->unique('slug', $this->table, null, $request->getAttribute('id'))
            ->slug('slug')
            ->length('slug', 5)
            ->length('name', 5)
            ->required($this->acceptedParams);
        if ($request->getAttribute('id') === null) {
            $validator->uploaded('image');
            $validator->uploaded('pdf');
        }
        return $validator;
    }
}
