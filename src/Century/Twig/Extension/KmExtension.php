<?php
namespace Century\Twig\Extension;

class KmExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'km' => new Twig_Filter_Method($this, 'km'),
        );
    }

    public function km($arg1)
    {

        return $arg1;
    }