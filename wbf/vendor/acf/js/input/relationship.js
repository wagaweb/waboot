(function($){
	
	acf.fields.relationship = acf.field.extend({
		
		type: 'relationship',
		
		$el: null,
		$input: null,
		$filters: null,
		$choices: null,
		$values: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'keypress [data-filter]': 			'submit_filter',
			'change [data-filter]': 			'change_filter',
			'keyup [data-filter]': 				'change_filter',
			'click .choices .acf-rel-item': 	'add_item',
			'click [data-name="remove_item"]': 	'remove_item'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-relationship');
			this.$input = this.$el.find('.acf-hidden input');
			this.$choices = this.$el.find('.choices'),
			this.$values = this.$el.find('.values');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// reference
			var self = this,
				$field = this.$field,
				$el = this.$el,
				$input = this.$input;
			
			
			// right sortable
			this.$values.children('.list').sortable({
			
				items:					'li',
				forceHelperSize:		true,
				forcePlaceholderSize:	true,
				scroll:					true,
				
				update:	function(){
					
					$input.trigger('change');
					
				}
				
			});
			
			
			this.$choices.children('.list').scrollTop(0).on('scroll', function(e){
				
				// bail early if no more results
				if( $el.hasClass('is-loading') || $el.hasClass('is-empty') ) {
				
					return;
					
				}
				
				
				// Scrolled to bottom
				if( $(this).scrollTop() + $(this).innerHeight() >= $(this).get(0).scrollHeight ) {
					
					var paged = parseInt( $el.attr('data-paged') );
					
					
					// update paged
					$el.attr('data-paged', (paged + 1) );
					
					
					// fetch
					self.doFocus($field);
					self.fetch();
				}
				
			});
			
			
			/*
var scroll_timer = null;
			var scroll_event = function( e ){
				
				console.log( 'scroll_event' );
				
				if( scroll_timer) {
					
			        clearTimeout( scroll_timer );
			        
			    }
			    
			    
			    scroll_timer = setTimeout(function(){
				    
				    
				    if( $field.is(':visible') && acf.is_in_view($field) ) {
						
						// fetch
						self.doFocus($field);
						self.fetch();
						
						
						$(window).off('scroll', scroll_event);
						
					}
				    
				    
			    }, 100);			    
			    				
				
			};
			
						
			$(window).on('scroll', scroll_event);
			
*/
			// ajax fetch values for left side
			this.fetch();
			
		},
		
		fetch: function(){
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// add class
			this.$el.addClass('is-loading');
			
			
			// vars
			var data = acf.prepare_for_ajax({
				action:		'acf/fields/relationship/query',
				field_key:	$field.data('key'),
				post_id:	acf.get('post_id'),
			});
			
			
			// merge in wrap data
			// don't use this.o becuase they are outdated
			$.extend(data, acf.get_data( this.$el ));
			
			
			// clear html if is new query
			if( data.paged == 1 ) {
				
				this.$choices.children('.list').html('')
				
			}
			
			
			// add message
			this.$choices.children('.list').append('<p>' + acf._e('relationship', 'loading') + '...</p>');

			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') ) {
			
				this.$el.data('xhr').abort();
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    
		    	url:		acf.get('ajaxurl'),
				dataType:	'json',
				type:		'post',
				data:		data,
				
				success: function( json ){
					
					// render
					self.doFocus($field);
					self.render(json);
					
				}
				
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render: function( json ){
			
			// remove loading class
			this.$el.removeClass('is-loading is-empty');
			
			
			// remove p tag
			this.$choices.children('.list').children('p').remove();
			
			
			// no results?
			if( !json || !json.length ) {
			
				// add class
				this.$el.addClass('is-empty');
			
				
				// add message
				if( this.o.paged == 1 ) {
				
					this.$choices.children('.list').append('<p>' + acf._e('relationship', 'empty') + '</p>');
			
				}

				
				// return
				return;
				
			}
			
			
			// get new results
			var $new = $( this.walker(json) );
			
				
			// apply .disabled to left li's
			this.$values.find('.acf-rel-item').each(function(){
				
				var id = $(this).attr('data-id');
				
				$new.find('.acf-rel-item[data-id="' + id + '"]').addClass('disabled');
				
			});
			
			
			// underline search match
			if( this.o.s ) {
			
				var s = this.o.s;
				
				$new.find('.acf-rel-item').each(function(){
					
					// vars
					var find = $(this).text(),
						replace = find.replace( new RegExp('(' + s + ')', 'gi'), '<b>$1</b>');
					
					$(this).html( $(this).html().replace(find, replace) );	
									
				});
				
			}
			
			
			// append
			this.$choices.children('.list').append( $new );
			
			
			// merge together groups
			var label = '',
				$list = null;
				
			this.$choices.find('.acf-rel-label').each(function(){
				
				if( $(this).text() == label ) {
					
					$list.append( $(this).siblings('ul').html() );
					
					$(this).parent().remove();
					
					return;
				}
				
				
				// update vars
				label = $(this).text();
				$list = $(this).siblings('ul');
				
			});
			
			
		},
		
		walker: function( data ){
			
			// vars
			var s = '';
			
			
			// loop through data
			if( $.isArray(data) ) {
			
				for( var k in data ) {
				
					s += this.walker( data[ k ] );
					
				}
				
			} else if( $.isPlainObject(data) ) {
				
				// optgroup
				if( data.children !== undefined ) {
					
					s += '<li><span class="acf-rel-label">' + data.text + '</span><ul class="acf-bl">';
					
						s += this.walker( data.children );
					
					s += '</ul></li>';
					
				} else {
				
					s += '<li><span class="acf-rel-item" data-id="' + data.id + '">' + data.text + '</span></li>';
					
				}
				
			}
			
			
			// return
			return s;
			
		},
		
		submit_filter: function( e ){
			
			// don't submit form
			if( e.which == 13 ) {
				
				e.preventDefault();
				
			}
			
		},
		
		change_filter: function( e ){
			
			// vars
			var val = e.$el.val(),
				filter = e.$el.attr('data-filter');
				
			
			// Bail early if filter has not changed
			if( this.$el.attr('data-' + filter) == val ) {
			
				return;
				
			}
			
			
			// update attr
			this.$el.attr('data-' + filter, val);
			
			
			// reset paged
			this.$el.attr('data-paged', 1);
		    
		    
		    // fetch
		    this.fetch();
			
		},
		
		add_item: function( e ){
			
			// max posts
			if( this.o.max > 0 ) {
			
				if( this.$values.find('.acf-rel-item').length >= this.o.max ) {
				
					alert( acf._e('relationship', 'max').replace('{max}', this.o.max) );
					
					return;
					
				}
				
			}
			
			
			// can be added?
			if( e.$el.hasClass('disabled') ) {
			
				return false;
				
			}
			
			
			// disable
			e.$el.addClass('disabled');
			
			
			// template
			var html = [
				'<li>',
					'<input type="hidden" name="' + this.$input.attr('name') + '[]" value="' + e.$el.attr('data-id') + '" />',
					'<span data-id="' + e.$el.attr('data-id') + '" class="acf-rel-item">' + e.$el.html(),
						'<a href="#" class="acf-icon small dark" data-name="remove_item"><i class="acf-sprite-remove"></i></a>',
					'</span>',
				'</li>'].join('');
						
			
			// add new li
			this.$values.children('.list').append( html )
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove_item : function( e ){
			
			// vars
			var $span = e.$el.parent(),
				id = $span.attr('data-id');
			
			
			// remove
			$span.parent('li').remove();
			
			
			// show
			this.$choices.find('.acf-rel-item[data-id="' + id + '"]').removeClass('disabled');
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
		}
		
	});
	

})(jQuery);
