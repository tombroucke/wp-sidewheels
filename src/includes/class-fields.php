<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

class Fields{

	function __construct(){
		
		$background = new FieldsBuilder('background');
		$background
		    ->addTab('Background')
		    ->addImage('background_image')
		    ->addTrueFalse('fixed')
		        ->instructions("Check to add a parallax effect where the background image doesn't move when scrolling")
		    ->addColorPicker('background_color');

		$banner = new FieldsBuilder('banner');
		$banner
		    ->addTab('Content')
		    ->addText('title')
		    ->addWysiwyg('content')
		    ->addFields($background)
		    ->setLocation('post_type', '==', 'page');

		$section = new FieldsBuilder('section');
		$section
		    ->addTab('Content')
		    ->addText('section_title')
		    ->addRepeater('columns', ['min' => 1, 'layout' => 'block'])
		        ->addTab('Content')
		        ->addText('title')
		        ->addWysiwyg('content')
		        ->addFields($background)
		        ->endRepeater()
		    ->addFields($background)
		    ->setLocation('post_type', '==', 'page');

	}

}