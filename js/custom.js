jQuery.noConflict();

jQuery(document).ready(function(){
	// activates the lightbox page, if you are using a dark color scheme use another theme parameter
	my_lightbox("a[rel^='prettyPhoto'], a[rel^='lightbox']",true);
	k_smoothscroll();
	
	k_menu(); // controls the dropdown menu
	
	// cufon font replacement
	font_improvement('h1, #featured:not(.curtain, .accordion, .newsslider) .sliderheading');
	
	// curtain slider
	jQuery(".curtain").curtain_prepare({
			effect: 'curtain', // wave, zipper, curtain
			strips: 30, // number of strips
			delay: 5000, // delay between images in ms
			stripDelay: 40, // delay beetwen strips in ms
			titleOpacity: 0.8, // opacity of title
			titleSpeed: 1000, // speed of title appereance in ms
			position: 'top', // top, bottom, alternate, curtain
			direction: 'fountainAlternate' // left, right, alternate, random, fountain, fountainAlternate
	});
	
	
	// accordion slider
	jQuery(".accordion").kricordion({
			slides: '.featured',		// wich element inside the container should serve as slide
			animationSpeed: 900,		// animation duration in milliseconds
			autorotation: true,			// autorotation true or false?
			autorotationSpeed:5,		// duration between autorotation switch in Seconds
			event: 'mouseover',			// event to focus a slide: mouseover or click
			imageShadow:true,			// should the image get a drop shadow to the left
			imageShadowStrength:0.4		// how dark should that shadow be, recommended values: between 0.3 and 0.8, allowed between 0 and 1
	});
	
	
	// fading slider
	jQuery('.fadeslider').kriesi_fade_slider({
			slides: '.featured',				// wich element inside the container should serve as slide
			animationSpeed: 900,				// animation duration in milliseconds
			autorotation: true,					// autorotation true or false?
			autorotationSpeed:5,				// duration between autorotation switch in Seconds
			appendControlls: '#feature_wrap'	//element to append the controlls to
	});
	
	
	// news slider
	jQuery('.newsslider').kriesi_news_slider({
			slides: '.featured',				// wich element inside the container should serve as slide
			animationSpeed: 900,				// animation duration in milliseconds
			autorotation: true,					// autorotation true or false?
			autorotationSpeed:5					// duration between autorotation switch in Seconds
	});

	jQuery('.ajax_form').kriesi_ajax_form();	// activates contact form
	jQuery('input:text').kriesi_empty_input();	// comment form improvement
	
	var selectImg = '#main';
	if (jQuery.browser.msie) selectImg = '.portfolio_item, .image_border'
	jQuery(selectImg).kriesi_image_preloader({delay:200});	// activates preloader for non-slideshow images
});



function font_improvement($selectors){

jQuery($selectors).each(function()
{
	$size = parseInt(jQuery(this).css('fontSize'));	
	jQuery(this).css('fontSize', $size * 1.4)
});

Cufon.replace($selectors,{ fontFamily: 'geosans' });
}



// -------------------------------------------------------------------------------------------
// input field improvements
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.kriesi_empty_input = function(options) 
	{
		return this.each(function()
		{
			var currentField = $(this);
			currentField.methods = 
			{
				startingValue:  currentField.val(),
				
				resetValue: function()
				{	
					var currentValue = currentField.val();
					if(currentField.methods.startingValue == currentValue) currentField.val('');
				},
				
				restoreValue: function()
				{	
					var currentValue = currentField.val();
					if(currentValue == '') currentField.val(currentField.methods.startingValue);
				}
			};
			
			currentField.bind('focus',currentField.methods.resetValue);
			currentField.bind('blur',currentField.methods.restoreValue);
		});
	}
})(jQuery);	


	
// -------------------------------------------------------------------------------------------
// The Image preloader
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.kriesi_image_preloader = function(variables, callback) 
	{
		var defaults = 
		{
			fadeInSpeed: 600,
			maxLoops: 10,
			callback: '',
			delay:500
		};
		
		var options = $.extend(defaults, variables);
			
		return this.each(function()
		{
			var container 	= $(this),
				images		= $('img', this).css({opacity:0, visibility:'visible', display:'block'}),
				parent = images.parent(),
				imageCount = images.length,
				interval = '',
				allImages = images ;
				
			
			var methods = 
			{
				checkImage: function()
				{
					images.each(function(i)
					{
						if(this.complete == true) images = images.not(this);
					});
					
					if(images.length && options.maxLoops >= 0)
					{
						options.maxLoops--;
						setTimeout(methods.checkImage, 500);
					}
					else
					{
						methods.showImages();
					}
				},
				
				showImages: function()
				{
					allImages.each(function(i)
					{
						var currentImage = $(this);
						setTimeout(function()
						{	
							currentImage.animate({opacity:1}, options.fadeInSpeed, function()
							{
								if(allImages.length == i+1) methods.callback(i);
							});
						},options.delay*(i+1));
						
					});
				},
				
				callback: function()
				{				
					if (variables instanceof Function) { callback = variables; }
					if (callback  instanceof Function) { callback.call(this);  }
					if(options.callback != '') (options.callback)();

				}
			};
			
			methods.checkImage();

		});
	};
})(jQuery);

// -------------------------------------------------------------------------------------------
// The Fade Slider
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.kriesi_fade_slider= function(options) 
	{
		var defaults = 
		{
			slides: '>div',				// wich element inside the container should serve as slide
			animationSpeed: 900,		// animation duration
			autorotation: true,			// autorotation true or false?
			autorotationSpeed:3,		// duration between autorotation switch in Seconds
			appendControlls: '',
			backgroundOpacity:0.8		// opacity for background
		};
		
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{
			var slideWrapper 	= $(this),								
				slides			= slideWrapper.find(options.slides).css({display:'none',zIndex:0}),
				slideCount 	= slides.length,
				currentSlideNumber = 0,
				interval,
				current_class = 'active_item',
				controlls = '',
				skipSwitch = true;
				
				slides.find('.feature_excerpt').css('opacity',options.backgroundOpacity);
				
				slideWrapper.methods = {
				
					init: function()
					{
						slides.filter(':eq(0)').css({zIndex:2, display:'block'});
						
						if(slideCount <= 1)
						{
							slideWrapper.kriesi_image_preloader({delay:200});
						}
						else
						{
							slideWrapper.kriesi_image_preloader({callback:slideWrapper.methods.preloadingDone, delay:200});
							
							if (options.appendControlls)
							{
								controlls = $('<div></div>').addClass('slidecontrolls').css({position:'absolute'}).appendTo(options.appendControlls);
								slides.each(function(i)
								{	
									var controller = $('<span class=" '+current_class+'"></span>').appendTo(controlls);
									controller.bind('click', {currentSlideNumber: i}, slideWrapper.methods.switchSlide);
									current_class = "";
								});	
							}
						}						
					},
					
					preloadingDone: function()
					{
						skipSwitch = false;
						
						if(options.autorotation)
						{
							slideWrapper.methods.autorotate();
						}
					},
					
					switchSlide: function(passed)
					{	
						var noAction = false;
						
						
						if(passed != undefined && !skipSwitch)
						{	
							if(currentSlideNumber != passed.data.currentSlideNumber)
							{	
								currentSlideNumber = passed.data.currentSlideNumber;
							}
							else
							{
								noAction = true;
							}
						}
						
						if(passed != undefined)
						{	
							clearInterval(interval);
						}
						
						if(!skipSwitch && noAction == false)
						{	
							skipSwitch = true;
							var currentSlide = slides.filter(':visible'),
								nextSlide = slides.filter(':eq('+currentSlideNumber+')');
								if(options.appendControlls)
								{	
									controlls.find('.active_item').removeClass('active_item');
									controlls.find('span:eq('+currentSlideNumber+')').addClass('active_item');									
								}
								
							currentSlide.css({zIndex:4});	
							nextSlide.css({zIndex:2, display:'block'});
							
							currentSlide.fadeOut(options.animationSpeed, function()
							{
								currentSlide.css({zIndex:0, display:"none"});
								skipSwitch = false;
							});
						}
					},
					
					autorotate: function()
					{	
						interval = setInterval(function()
						{ 	
							currentSlideNumber ++;
							if(currentSlideNumber == slideCount) currentSlideNumber = 0;
							
							slideWrapper.methods.switchSlide();
						},
						(parseInt(options.autorotationSpeed) * 1000));
					}
				
				};
				
				slideWrapper.methods.init();
		});
		
	}
})(jQuery);

// -------------------------------------------------------------------------------------------
// The News Slider
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.kriesi_news_slider= function(options) 
	{
		var defaults = 
		{
			slides: '>div',				// wich element inside the container should serve as slide
			animationSpeed: 900,		// animation duration
			autorotation: true,			// autorotation true or false?
			autorotationSpeed:3,		// duration between autorotation switch in Seconds
			easing: 'easeOutQuint',
			backgroundOpacity:0.8		// opacity for background
		};
		
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{
			var slideWrapper 	= $(this),								
				slides			= slideWrapper.find(options.slides).css({display:'none',zIndex:0}),
				slideCount 	= slides.length,
				accelerator = 0,				// accelerator of scrolling speed
				scrollInterval = '',			// var that stores the setInterval id
				mousePos = '',					// current mouse position
				moving = false,					// scrollbar currently moving or not?
				controllWindowHeight = 0,		// height of the wrapping element that hides overflow
				controllWindowPart = 0,			// mouseoverpart of the wrapping element that hides overflow
				itemWindowHeight = 0,			// height of element to move
				current_class = 'active_item',
				skipSwitch = true,
				currentSlideNumber = 0,
				newsSelect ='',
				newsItems = '';		
				
				slides.find('.feature_excerpt').css('opacity',options.backgroundOpacity);		
				
				slideWrapper.methods = {
				
					init: function()
					{
						newsSelect = $('<div></div>').addClass('newsselect').appendTo(slideWrapper);
						newsItems = $('<div></div>').addClass('newsItems').appendTo(newsSelect);
						fadeout = $('<div></div>').addClass('fadeout').addClass('').appendTo(slideWrapper);
						
						slides.filter(':eq(0)').css({zIndex:2, display:'block'});
						
						slides.each(function(i)
						{	
							var slide = $(this),
								url = slide.find('a').attr('href'),
								controll = $('<a class="single_item '+current_class+'"></a>').appendTo(newsItems).attr('href',url);
								current_class ='';
								
							slide.find('.feature_excerpt .sliderheading, .feature_excerpt .sliderdate').clone().appendTo(controll);
							controll.bind('click', {currentSlideNumber: i}, slideWrapper.methods.switchSlide);
						});
						
						controllWindowHeight = newsSelect.height();
						controllWindowPart = controllWindowHeight/3;
						itemWindowHeight = newsItems.height();
						
						if(slideCount > 1)
						{
							slideWrapper.kriesi_image_preloader({delay:200});
							slideWrapper.methods.preloadingDone();
						}
					},
					
					preloadingDone: function()
					{	
						skipSwitch = false;
						var offset = newsSelect.offset();
						
						newsSelect.mousemove(function(e)
						{
							mousePos = e.pageY - offset.top;
							
							if(!moving)
							{
								scrollInterval = setInterval(function() { slideWrapper.methods.scrollItem(mousePos); }, 25);
								moving = true;
							}
						}); 
										
						newsSelect.bind('mouseleave', function()
						{ 
							clearInterval(scrollInterval); 
							moving = false;
							accelerator = 0;
						});
						
					},
					
					scrollItem: function()
					{	
						var movement = 0,
							percent = controllWindowPart / 100,
							modifier = 10,
							currentTop = parseInt(newsItems.css('top'));
							
						accelerator = accelerator <= 2 ? accelerator + 0.5 : accelerator; 
						
						if(mousePos < controllWindowPart)
						{	
							movement = ((controllWindowPart - mousePos) / percent) * accelerator;
							newPos = currentTop + (movement/modifier);
							
							if(currentTop < 0)
							{	
								if (newPos > 0) newPos = 0;
								newsItems.css({top:newPos + "px"});
							}
						}
						else if(mousePos > controllWindowPart * 2)
						{	
							movement = ((mousePos - controllWindowPart * 2) / percent) * accelerator;
							newPos = currentTop + (movement/modifier * -1);
							
							if((currentTop * -1) < itemWindowHeight - controllWindowHeight)
							{
								if (newPos * -1 > itemWindowHeight - controllWindowHeight) newPos = controllWindowHeight - itemWindowHeight;
								
								newsItems.css({top:newPos + "px"});
							}
						}
						else
						{
							accelerator = 0;
						}
					},
					
					switchSlide: function(passed)
					{	
						
						var noAction = false;
						
						if(passed != undefined && !skipSwitch)
						{
							if(currentSlideNumber != passed.data.currentSlideNumber)
							{	
								currentSlideNumber = passed.data.currentSlideNumber;
							}
							else
							{
								noAction = true;
							}
						}
						
						if(passed != undefined)
						{	
							// clearInterval(interval);
						}
						
						
						if(!skipSwitch && noAction == false)
						{	
							skipSwitch = true;
							var currentSlide = slides.filter(':visible'),
								nextSlide = slides.filter(':eq('+currentSlideNumber+')');
																
							newsSelect.find('.active_item').removeClass('active_item');
							newsSelect.find('a:eq('+currentSlideNumber+')').addClass('active_item');	
																
							currentSlide.css({zIndex:4});
							nextSlide.css({zIndex:2, display:'block'});
							
							currentSlide.fadeOut(options.animationSpeed, function()
							{
								currentSlide.css({zIndex:0, display:"none"});
								skipSwitch = false;
							});
						}
						return false;
					},
					
					autorotate: function()
					{	
						//autorotation not yet supportet
					}
				
				};
				
				slideWrapper.methods.init();
		});
		
	}
})(jQuery);

// -------------------------------------------------------------------------------------------
// Curtain slider prearation
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.curtain_prepare= function(options) 
	{
		return this.each(function()
		{	
			var slideWrapper = $(this);
			options.width = slideWrapper.width(),
			options.height = slideWrapper.height();
			
			slideWrapper.find('.featured').each(function()
			{	
				var permaLink = $(this).find('a').attr('href'),
					infoText = $(this).find('.feature_excerpt'),
					posChange ='';
					
					if (permaLink == undefined) permaLink ='#'
					if(jQuery.browser.msie) {posChange = 'rel_pos'; options.titleOpacity = 0.85;}
					
					$(this).find('img').attr('alt','<a href="'+permaLink+'"><span class="feature_excerpt '+posChange+'">'+infoText.html()+'</span></a>');
					infoText.remove();
			});
			
			jQuery('#featured').kriesi_image_preloader({callback:add_jqFancyTransitions});
			
			function add_jqFancyTransitions()
			{	
				slideWrapper.find('.featured').addClass('noborder')
				slideWrapper.jqFancyTransitions(options);
			}
		});
	}
})(jQuery);
// -------------------------------------------------------------------------------------------
// The Main accordion slider - KRICORDION

// Dependencies: equalheight function, kriesi_image_preoloader. jquery easing
//
// -------------------------------------------------------------------------------------------
(function($)
{
	$.fn.kricordion = function(options) 
	{
		var defaults = 
		{
			slides: '>div',				// wich element inside the container should serve as slide
			animationSpeed: 900,		// animation duration
			autorotation: true,			// autorotation true or false?
			autorotationSpeed:3,		// duration between autorotation switch in Seconds
			easing: 'easeOutQuint',		// animation easing, more options at the bottom of this file
			event: 'mouseover',			// event to focus a slide: mouseover or click
			imageShadow:true,			// should the image get a drop shadow to the left
			imageShadowStrength:0.5,	// how dark should that shadow be, recommended values: between 0.3 and 0.8, allowed between 0 and 1
			fontOpacity: 1,				// opacity for font, if set to 1 it will be stronger but most browsers got a small rendering glitch at 1
			backgroundOpacity: 0.8
		};
		
		// merge default values with the values that were passed with the function call
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{	
			// save some jQuery selections into variables, also calculate base values for each slide
			var slideWrapper 	= $(this),								// element that holds the slides
				slides			= slideWrapper.find(options.slides).css('display','block'),	// the slides
				slide_count 	= slides.length,						// number of slides
				slide_width		= slideWrapper.width() / slide_count	// width of the slides
				expand_slide 	= slides.width(),						// size of a slide when expanded, defined in css, class ".featured" by default
				minimized_slide	= (slideWrapper.width() - expand_slide) / (slide_count - 1), // remaining width is shared among the non-active slides
				overlay_modifier = 200 *(1- options.imageShadowStrength),					//increases the size of the minimized image div to avoid flickering
				excerptWrapper = slideWrapper.find('.feature_excerpt'),
				interval = '',
				current_slide = 0;
			
				
			//modify excerptWrapper and re-select it, also add positioning span -------------------------
			excerptWrapper.wrap('<span class="feature_excerpt"></span>').removeClass('feature_excerpt').addClass('position_excerpt');
			excerptWrapper = slideWrapper.find('.feature_excerpt').css('opacity',options.backgroundOpacity);
			// -------------------------------------------------------------------------------------------
			
				
			//equal heights for all excerpt containers, then hide basic excerpt content -----------------
			excerptWrapper.equalHeights().find('.position_excerpt').css({display:'block', opacity:0, position:'absolute'});
			var excerptWrapperHeight = excerptWrapper.height();
			// -------------------------------------------------------------------------------------------
			
						
			
			//iterate each slide and set new base values, also set positions for acitve and inactive states and event handlers
			slides.each(function(i)
			{
				var this_slide = $(this),											// current slide element
					this_slide_a = this_slide.find('a'),							// a tag inside the element
					real_excerpt = this_slide.find('.position_excerpt'),			// wrapper to center the excerpt content verticaly
					real_excerpt_height = real_excerpt.height(),					// height of the excerpt content
					slide_heading =this_slide.find('.sliderheading'),  				// slide heading
					cloned_heading =   slide_heading.clone().appendTo(this_slide_a) // clone heading for heading only view
													.addClass('heading_clone')
													.css({opacity:options.fontOpacity, width:slide_width-30}),
					clone_height = cloned_heading.height();							// height of clone heading, needed to center verticaly as well
					
					
					this_slide.css('backgroundPosition',parseInt(slide_width/2-8) + 'px ' + parseInt((this_slide.height()- excerptWrapperHeight)/2 -8) + 'px');						
					
					cloned_heading.css({bottom: (excerptWrapperHeight-clone_height)/2 +9});			//center clone heading
					real_excerpt.css({bottom: (excerptWrapperHeight-real_excerpt_height)/2 +9});	//center real excerpt
												
					this_slide.data( //save data of each slide via jquerys data method
					'data',
					{
						this_slides_position: i * slide_width,							// position if no item is active
						pos_active_higher: i * minimized_slide,							// position of the item if a higher item is active
						pos_active_lower: ((i-1) * minimized_slide) + expand_slide		// position of the item if a lower item is active
					});
					
				//set base properties	
				this_slide.css({zIndex:i+1, left: i * slide_width, width:slide_width + overlay_modifier});
								
				
				//apply the fading div if option is set to do so
				if(options.imageShadow)
				{
					this_slide.find('>a').prepend('<span class="fadeout "></span>');
				}
								
			});
			
			// calls the preloader, kriesi_image_preloader plugin needed
			jQuery('#featured').kriesi_image_preloader({callback:add_functionality});
			
			function add_functionality()
			{
				
				//set autorotation ---------------------------------------------------------------------------
				
				
				if(options.autorotation)
				{
					interval = setInterval(function() { autorotation(); }, (parseInt(options.autorotationSpeed) * 1000));
				}
				
				slides.each(function(i)
				{	
					var this_slide = $(this), 
						real_excerpt = this_slide.find('.position_excerpt'), 
						cloned_heading = this_slide.find('.heading_clone');
						
					//set mouseover or click event
					this_slide.bind(options.event, function(event, continue_autoslide)
					{	
						//stop autoslide on userinteraction
						if(!continue_autoslide)
						{
							clearInterval(interval)
						}
						
						var objData = this_slide.data( 'data' );
						//on mouseover expand current slide to full size and fadeIn real content
						real_excerpt.stop().animate({opacity:options.fontOpacity},options.animationSpeed, options.easing);
						cloned_heading.stop().animate({opacity:0},options.animationSpeed, options.easing);
						
						this_slide.stop().animate({	width: expand_slide + (overlay_modifier * 1.2), 
													left: objData.pos_active_higher},
													options.animationSpeed, options.easing);
											
						//set and all other slides to small size
						slides.each(function(j){
						
							if (i !== j)
							{	
								var this_slide = $(this),
									real_excerpt = this_slide.find('.position_excerpt'),
									cloned_heading = this_slide.find('.heading_clone'),
									objData = this_slide.data( 'data' ),
									new_pos = objData.pos_active_higher;
									
								if(i < j) { new_pos = objData.pos_active_lower; }
								this_slide.stop().animate({left: new_pos, width:minimized_slide + overlay_modifier},options.animationSpeed, options.easing);
								real_excerpt.stop().animate({opacity:0},options.animationSpeed, options.easing);
								cloned_heading.stop().animate({opacity:options.fontOpacity},options.animationSpeed, options.easing);
							}
						
						});
						
					});
				});
				
				
				//set mouseout event: expand all slides to no-slide-active position and width
				slideWrapper.bind('mouseleave', function()
				{
					slides.each(function(i)
					{
						var this_slide = $(this),
							real_excerpt = this_slide.find('.position_excerpt'),
							cloned_heading = this_slide.find('.heading_clone'),
							objData = this_slide.data( 'data' ),
							new_pos = objData.this_slides_position;
							
							this_slide.stop().animate({left: new_pos, width:slide_width + overlay_modifier},options.animationSpeed, options.easing);
							real_excerpt.stop().animate({opacity:0},options.animationSpeed, options.easing);
							cloned_heading.stop().animate({opacity:options.fontOpacity},options.animationSpeed, options.easing);
					});
					
				});
			}
			
			
			// autorotation function for the image slider
			function autorotation()
			{	
				if(slide_count  == current_slide)
				{
					slideWrapper.trigger('mouseleave');
					current_slide = 0;
				}
				else
				{
					slides.filter(':eq('+current_slide+')').trigger(options.event,[true]);
					current_slide ++;
				}
			}
		});
	};
})(jQuery);
// -------------------------------------------------------------------------------------------
// END KRICORDION
// -------------------------------------------------------------------------------------------



function k_menu()
{
	// k_menu controlls the dropdown menus and improves them with javascript
	
	jQuery("#nav a").removeAttr('title');
	jQuery(" #nav ul").css({display: "none"});
	
	//smooth drop downs
	jQuery("#nav li").each(function()
	{	
		var $sublist = jQuery(this).find('ul:first');
		
		$sublist.find('>li:first').css('marginTop','5px');
		$sublist.find('>li:last').css('marginBottom','5px');
		
		jQuery(this).hover(function()
		{	
			$sublist.stop().css({overflow:"hidden", height:"auto", display:"none",'paddingTop':'0px','paddingBottom':'0px'}).slideDown(400, function()
			{
				jQuery(this).css({overflow:"visible", height:"auto"});
			});
		},
		function()
		{	
			$sublist.stop().slideUp(400, function()
			{	
				jQuery(this).css({overflow:"hidden", display:"none"});
			});
		});	
	});
}


//equalHeights by james padolsey
jQuery.fn.equalHeights = function() {
    return this.height(Math.max.apply(null,
        this.map(function() {
           return jQuery(this).height()
        }).get()
    ));
};




function my_lightbox($elements, autolink)
{	
	var theme_selected = 'light_rounded';
	
	if(autolink)
	{
		jQuery('a[href$=jpg], a[href$=png], a[href$=gif], a[href$=jpeg], a[href$=.mov] , a[href$=.swf] , a[href*=vimeo.com] , a[href*=youtube.com]').contents("img").parent().each(function()
		{
			if(!jQuery(this).attr('rel') != undefined && !jQuery(this).attr('rel') != '' && !jQuery(this).hasClass('noLightbox'))
			{
				jQuery(this).attr('rel','lightbox[auto_group]')
			}
		});
	}
	
	jQuery($elements).prettyPhoto({
			"theme": theme_selected /* light_rounded / dark_rounded / light_square / dark_square */																	});
	
	jQuery($elements).each(function()
	{	
		var $image = jQuery(this).contents("img");
		$newclass = 'lightbox_video';
		
		if(jQuery(this).attr('href').match(/(jpg|gif|jpeg|png|tif)/)) $newclass = 'lightbox_image';
			
		if ($image.length > 0)
		{	
			if(jQuery.browser.msie &&  jQuery.browser.version < 7) jQuery(this).addClass('ie6_lightbox');
			
			var $bg = jQuery("<span class='"+$newclass+" '></span>").appendTo(jQuery(this));
			
			jQuery(this).bind('mouseenter', function()
			{
				$height = $image.height();
				$width = $image.width();
				$pos =  $image.position();		
				$bg.css({height:$height, width:$width, top:$pos.top, left:$pos.left});
			});
		}
	});	
	
	jQuery($elements).contents("img").hover(function()
	{
		jQuery(this).stop().animate({opacity:0.5},400);
	},
	function()
	{
		jQuery(this).stop().animate({opacity:1},400);
	});
}


(function($)
{
	$.fn.kriesi_ajax_form = function(options) 
	{
		var defaults = 
		{
			sendPath: 'send.php',
			responseContainer: '.ajaxresponse'
		};
		
		var options = $.extend(defaults, options);
		
		return this.each(function()
		{
			var form = $(this),
				send = 
				{
					formElements: form.find('textarea, select, input:text, input[type=hidden]'),
					validationError:false,
					button : form.find('input:submit'),
					datastring : ''
				};
			
			send.button.bind('click', checkElements);
			
			function send_ajax_form()
			{
				send.button.fadeOut(300);	
									
				$.ajax({
					type: "POST",
					url: options.sendPath,
					data:send.datastring,
					success: function(response)
					{	
						
						var message =  $("<div'></div>").addClass(options.responseContainer)
														.css('display','none')
														.insertBefore(form)
														.html(response); 
														
						form.slideUp(400, function(){message.slideDown(400), send.formElements.val('');});
						
					}
				});
				
			}
			
			function checkElements()
			{	
				// reset validation var and send data
				send.validationError = false;
				send.datastring = 'ajax=true';
				
				send.formElements.each(function(i)
				{
					var currentElement = $(this),
						surroundingElement = currentElement.parent(),
						value = currentElement.val(),
						name = currentElement.attr('name'),
					 	classes = currentElement.attr('class'),
					 	nomatch = true;
					 	
					 	send.datastring  += "&" + name + "=" + value;
					 	
					 	if(classes.match(/is_empty/))
						{
							if(value == '')
							{
								surroundingElement.attr("class","").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.attr("class","").addClass("valid");
							}
							nomatch = false;
						}
						
						if(classes.match(/is_email/))
						{
							if(!value.match(/^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$/))
							{
								surroundingElement.attr("class","").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.attr("class","").addClass("valid");
							}	
							nomatch = false;
						}
						
						if(nomatch && value != '')
						{
							surroundingElement.attr("class","").addClass("valid");
						}
				});
				
				if(send.validationError == false)
				{
					send_ajax_form();
				}
				return false;
			}
		});
	}
})(jQuery);



function k_smoothscroll()
{
	jQuery('a[href*=#]').click(function() {
		
	   // duration in ms
	
	   var newHash=this.hash,
		   target=jQuery(this.hash).offset().top,
		   oldLocation=window.location.href.replace(window.location.hash, ''),
		   newLocation=this,
		   duration=800,
		   easing='easeOutQuint';
		
		
	   // make sure it's the same location      
	   if(oldLocation+newHash==newLocation)
	   {
	      // animate to target and set the hash to the window.location after the animation
	      jQuery('html:not(:animated),body:not(:animated)').animate({ scrollTop: target }, duration, easing, function() {
	
	         // add new hash to the browser location
	         window.location.href=newLocation;
	      });
	
	      // cancel default click action
	      return false;
	   }
	
	});
}




/*
 * jQuery Easing v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/
*/

// t: current time, b: begInnIng value, c: change In value, d: duration
jQuery.easing['jswing'] = jQuery.easing['swing'];

jQuery.extend( jQuery.easing,
{
	def: 'easeOutQuad',
	swing: function (x, t, b, c, d) {
		//alert(jQuery.easing.default);
		return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
	},
	easeInQuad: function (x, t, b, c, d) {
		return c*(t/=d)*t + b;
	},
	easeOutQuad: function (x, t, b, c, d) {
		return -c *(t/=d)*(t-2) + b;
	},
	easeInOutQuad: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t + b;
		return -c/2 * ((--t)*(t-2) - 1) + b;
	},
	easeInCubic: function (x, t, b, c, d) {
		return c*(t/=d)*t*t + b;
	},
	easeOutCubic: function (x, t, b, c, d) {
		return c*((t=t/d-1)*t*t + 1) + b;
	},
	easeInOutCubic: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t + b;
		return c/2*((t-=2)*t*t + 2) + b;
	},
	easeInQuart: function (x, t, b, c, d) {
		return c*(t/=d)*t*t*t + b;
	},
	easeOutQuart: function (x, t, b, c, d) {
		return -c * ((t=t/d-1)*t*t*t - 1) + b;
	},
	easeInOutQuart: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
		return -c/2 * ((t-=2)*t*t*t - 2) + b;
	},
	easeInQuint: function (x, t, b, c, d) {
		return c*(t/=d)*t*t*t*t + b;
	},
	easeOutQuint: function (x, t, b, c, d) {
		return c*((t=t/d-1)*t*t*t*t + 1) + b;
	},
	easeInOutQuint: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
		return c/2*((t-=2)*t*t*t*t + 2) + b;
	},
	easeInSine: function (x, t, b, c, d) {
		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
	},
	easeOutSine: function (x, t, b, c, d) {
		return c * Math.sin(t/d * (Math.PI/2)) + b;
	},
	easeInOutSine: function (x, t, b, c, d) {
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	},
	easeInExpo: function (x, t, b, c, d) {
		return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
	},
	easeOutExpo: function (x, t, b, c, d) {
		return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
	},
	easeInOutExpo: function (x, t, b, c, d) {
		if (t==0) return b;
		if (t==d) return b+c;
		if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
		return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
	},
	easeInCirc: function (x, t, b, c, d) {
		return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
	},
	easeOutCirc: function (x, t, b, c, d) {
		return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
	},
	easeInOutCirc: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
		return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
	},
	easeInElastic: function (x, t, b, c, d) {
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
	},
	easeOutElastic: function (x, t, b, c, d) {
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
	},
	easeInOutElastic: function (x, t, b, c, d) {
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
	},
	easeInBack: function (x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		return c*(t/=d)*t*((s+1)*t - s) + b;
	},
	easeOutBack: function (x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
	},
	easeInOutBack: function (x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158; 
		if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
		return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
	},
	easeInBounce: function (x, t, b, c, d) {
		return c - jQuery.easing.easeOutBounce (x, d-t, 0, c, d) + b;
	},
	easeOutBounce: function (x, t, b, c, d) {
		if ((t/=d) < (1/2.75)) {
			return c*(7.5625*t*t) + b;
		} else if (t < (2/2.75)) {
			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
		} else if (t < (2.5/2.75)) {
			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
		} else {
			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
		}
	},
	easeInOutBounce: function (x, t, b, c, d) {
		if (t < d/2) return jQuery.easing.easeInBounce (x, t*2, 0, c, d) * .5 + b;
		return jQuery.easing.easeOutBounce (x, t*2-d, 0, c, d) * .5 + c*.5 + b;
	}
});


/**
 * jqFancyTransitions - jQuery plugin
 * @version: 1.0 (2009/12/04)
 * @requires jQuery v1.2.2 or later 
 * @author Ivan Lazarevic
 * Examples and documentation at: http://www.workshop.rs/projects/jqfancytransitions
 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
**/

(function($){var opts=new Array;var level=new Array;var img=new Array;var titles=new Array;var order=new Array;var imgInc=new Array;var inc=new Array;var stripInt=new Array;var imgInt=new Array;$.fn.jqFancyTransitions=$.fn.jqFancyTransitions=function(options){init=function(el){opts[el.id]=$.extend({},$.fn.jqFancyTransitions.defaults,options);img[el.id]=new Array();titles[el.id]=new Array();order[el.id]=new Array();imgInc[el.id]=0;inc[el.id]=0;params=opts[el.id];if(params.effect=='zipper'){params.direction='alternate';params.position='alternate';}
if(params.effect=='wave'){params.direction='alternate';params.position='top';}
if(params.effect=='curtain'){params.direction='alternate';params.position='curtain';}
stripWidth=parseInt(params.width/params.strips);gap=params.width-stripWidth*params.strips;stripLeft=0;$.each($('#'+el.id+' img'),function(i,item){img[el.id][i]=$(item).attr('src');titles[el.id][i]=$(item).attr('alt')?$(item).attr('alt'):'';$(item).hide();});$('#'+el.id).css({'background-image':'url('+img[el.id][0]+')','width':params.width,'height':params.height,'position':'relative','background-position':'top left'});$('#'+el.id).append("<div class='ft-title' id='ft-title-"+el.id+"' style='position: absolute; bottom:0; left: 0; z-index: 1000; color: #fff; background-color: #000; '>"+titles[el.id][0]+"</div>");if(titles[el.id][imgInc[el.id]])
$('#ft-title-'+el.id).css('opacity',opts[el.id].titleOpacity);else
$('#ft-title-'+el.id).css('opacity',0);odd=1;for(j=1;j<params.strips+1;j++){if(gap>0){tstripWidth=stripWidth+1;gap--;}else{tstripWidth=stripWidth;}
$('#'+el.id).append("<div class='ft-"+el.id+"' id='ft-"+el.id+j+"' style='width:"+tstripWidth+"px; height:"+params.height+"px; float: left; position: absolute;'></div>");$("#ft-"+el.id+j).css({'background-position':-stripLeft+'px top','left':stripLeft});stripLeft+=tstripWidth;if(params.position=='bottom')
$("#ft-"+el.id+j).css('bottom',0);if(j%2==0&&params.position=='alternate')
$("#ft-"+el.id+j).css('bottom',0);if(params.direction=='fountain'||params.direction=='fountainAlternate'){order[el.id][j-1]=parseInt(params.strips/2)-(parseInt(j/2)*odd);order[el.id][params.strips-1]=params.strips;odd*=-1;}else{order[el.id][j-1]=j;}}
$('.ft-'+el.id).mouseover(function(){opts[el.id].pause=true;});$('.ft-'+el.id).mouseout(function(){opts[el.id].pause=false;});$('#ft-title-'+el.id).mouseover(function(){opts[el.id].pause=true;});$('#ft-title-'+el.id).mouseout(function(){opts[el.id].pause=false;});clearInterval(imgInt[el.id]);imgInt[el.id]=setInterval(function(){$.transition(el)},params.delay+params.stripDelay*params.strips);};$.transition=function(el){if(opts[el.id].pause==true)return;stripInt[el.id]=setInterval(function(){$.strips(order[el.id][inc[el.id]],el)},opts[el.id].stripDelay);$('#'+el.id).css({'background-image':'url('+img[el.id][imgInc[el.id]]+')'});imgInc[el.id]++;if(imgInc[el.id]==img[el.id].length){imgInc[el.id]=0;}
if(titles[el.id][imgInc[el.id]]!=''){$('#ft-title-'+el.id).animate({opacity:0},opts[el.id].titleSpeed,function(){$(this).html(titles[el.id][imgInc[el.id]]).animate({opacity:opts[el.id].titleOpacity},opts[el.id].titleSpeed);});}else{$('#ft-title-'+el.id).animate({opacity:0},opts[el.id].titleSpeed);}
inc[el.id]=0;if(opts[el.id].direction=='random')
$.fisherYates(order[el.id]);if((opts[el.id].direction=='right'&&order[el.id][0]==1)||opts[el.id].direction=='alternate'||opts[el.id].direction=='fountainAlternate')
order[el.id].reverse();};$.strips=function(itemId,el){temp=opts[el.id].strips;if(inc[el.id]==temp){clearInterval(stripInt[el.id]);return;}
if(opts[el.id].position=='curtain'){currWidth=$('#ft-'+el.id+itemId).width();$('#ft-'+el.id+itemId).css({width:0,opacity:0,'background-image':'url('+img[el.id][imgInc[el.id]]+')'});$('#ft-'+el.id+itemId).animate({width:currWidth,opacity:1},1000);}else{$('#ft-'+el.id+itemId).css({height:0,opacity:0,'background-image':'url('+img[el.id][imgInc[el.id]]+')'});$('#ft-'+el.id+itemId).animate({height:opts[el.id].height,opacity:1},1000);}
inc[el.id]++;};$.fisherYates=function(arr){var i=arr.length;if(i==0)return false;while(--i){var j=Math.floor(Math.random()*(i+1));var tempi=arr[i];var tempj=arr[j];arr[i]=tempj;arr[j]=tempi;}}
this.each(function(){init(this);});};$.fn.jqFancyTransitions.defaults={width:500,height:332,strips:20,delay:5000,stripDelay:50,titleOpacity:0.7,titleSpeed:1000,position:'alternate',direction:'fountainAlternate',effect:''};})(jQuery);


