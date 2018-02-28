<?php
namespace App\Shop\Upload;

use Virton\Upload;

class ProductImageUpload extends Upload
{
    protected $path = 'public/uploads/products';
    protected $formats = [
        'thumb' => [480, 320]
    ];
}
