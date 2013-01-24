<?php
namespace Century\Twig\Extension;

class MilesExtension extends \Twig_Filter_Function
{
    public function getName() {
        return "miles";
    }

    public function getFunctions()
    {
        return array(
            'miles' => new \Twig_Filter_Method($this, 'miles'),
        );
    }

    public function miles($arg1)
    {
        $miles = round((int) $arg1 * 0.621371192, 1);

        return $miles.'mi';
    }
}