<?php

namespace Century\Form\DataTransformer;
use Silex\Application;
use Symfony\Component\Form\DataTransformerInterface;

class KmToMilesDataTransformer implements DataTransformerInterface
{	
	private $app;
	public function __construct(Application $app){
		$this->app = $app;
	}
	public function transform($data)
    {
    	//return $km * 0.621371192;
     	//return ((double)$miles * 1.609344);0.621371192
     	print_r($data);
		$data['km'] = $data['km'] * 0.621371192;
    	$data['average_speed'] = $data['average_speed'] * 0.621371192;
    	return $data;
    }
	public function reverseTransform($data)
    {
    	//$data['km'] = $data['km'] * 1.609344;
    	//$data['average_speed'] = $data['average_speed'] * 1.609344;
    	return $data;
    }
}