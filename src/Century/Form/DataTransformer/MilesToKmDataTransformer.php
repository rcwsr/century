<?php

namespace Century\Form\DataTransformer;
use Silex\Application;
use Symfony\Component\Form\DataTransformerInterface;

class MilesToKmDataTransformer implements DataTransformerInterface
{	
	private $app;
	public function __construct(Application $app){
		$this->app = $app;
	}
	public function transform($km)
    {
    	//return $km * 0.621371192;
     	//return ((double)$miles * 1.609344);0.621371192
     	
		//return $km * 1.609344;
        return '';
    	
    }
	public function reverseTransform($data)
    {
    	//$data['km'] = $data['km'] * 1.609344;
    	//$data['average_speed'] = $data['average_speed'] * 1.609344;
    	return $data;
    }
}