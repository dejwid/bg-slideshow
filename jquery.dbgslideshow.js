/*
Very simple slideshow plugins wwith fade effect
@author: Dawid Paszko
@www: http://www.dejwid.pl/
*/
(function( $ ){
	$.fn.dslideshow = function($options) {

		if(!$options){
			$options = {};
		}
		if(!$options.delay) $options.delay = 4000;
		if(!$options.speed) $options.speed = 4000;
		if(!$options.slides) $options.slides = '.slide';
		$slides = this.children($options.slides);
		$slidesc = $slides.length;
		//set-up first slide
		$slides.hide();//hide all
		$slides.eq($slidesc-1).after($slides.eq(0));//move first
		$slides.eq($slidesc-1).show();//show neighbour
		$slides.eq(0).show();//and slide
		//define the loop
		function slideLoop($id){
			//swap
			$prevId = ($id==0)?$slidesc-1:$id-1;
			$slides.eq($prevId).after($slides.eq($id));
			$slides.not($slides.eq($prevId)).hide();
			//effect
			$slides.eq($id).delay($options.delay).fadeIn($options.speed,function(){
				$next = ($id==$slidesc-1)?0:$id+1;
				slideLoop($next);
			});
		}
		//and start them :)
		slideLoop(1);
	};
})( jQuery );