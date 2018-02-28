<?php
namespace Virton\Twig;

class ProgressBarExtension extends \Twig_Extension
{
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('progress', [$this, 'progress'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param int|string $maxRange
     * @param int|string $currentRange
     * @param bool $stripped
     * @param string $classes
     * @return string
     */
    public function progress($maxRange, $currentRange, bool $stripped = false, string $classes = ''): string
    {

        $minRange = 0;
        $maxRange = (int)$maxRange;
        $currentRange = (int)$currentRange;
        $percentRange = (int)(($currentRange / $maxRange) * 100);

        $stats = ($percentRange < 100) ? ' bg-danger' : ' bg-success';

        $stripped = $stripped ? 'progress-bar-striped' : null;

        return "
         <div class=\"progress $classes\">
            <div class=\"progress-bar $stripped $stats\"
                style=\"width: $percentRange%\"
                role=\"progressbar\"
                aria-valuenow=\"$currentRange\"
                aria-valuemin=\"$minRange\"
                aria-valuemax=\"$maxRange\">
               $currentRange/$maxRange
           </div>
       </div>
        ";
    }
}
