
(function( $ ) {
	
	var is_rtl = false;
	var rtl_direction = 1;
	
	// ---------------
	// Functions start
	// ---------------
	
	// Base functions 
	 
	window.bt_parse_float = function( x ) {
		r = parseFloat( x );
		if ( isNaN( r ) ) r = 0;
		return r;
	}
	
	$.fn.bt_cc_lock = function(){
		$(this).addClass( 'btCCLock' );
		return $(this);
	}
	
	$.fn.bt_cc_unlock = function(){
		$(this).removeClass( 'btCCLock' );
		return $(this);
	}
	
	// function to be called from element conditions 

	window.bt_cc_set_slider_value = function( elem, value, calc ){
		if ( elem.slider( 'instance' ) ) { 
			elem.slider( 'value', value );
			elem.find('.btQuoteSliderValue').html( value );	
			if ( calc ) {
				bt_quote_total( elem.closest( '.btQuoteBooking' ) );
				bt_paypal_items( elem.closest( '.btQuoteBooking' ) );
			}
		}

	}
	window.bt_cc_get_slider_value = function( elem ){
		if ( elem.slider( 'instance' ) ) {
			// console.log( 'elem.slider_html: ' + elem.find('.btQuoteSliderValue').html() );
			// val = bt_parse_float( window.rtl_direction * elem.slider( 'value' ) );
			val = bt_parse_float( elem.find('.btQuoteSliderValue').html() );
		} else {
			val = bt_parse_float( elem.data( 'initial-value' ) );
		}
		// console.log( 'bt_cc_get_slider_value: ' + val );
		return val;
		
	}
	
	$.fn.bt_cc_set_value = function( value ){
		if ( value !== null && value !== 'undefined' && value !== undefined ) {
			var initial_value = value;	
		} else {
			var initial_value = $( this ).find( '.btQuoteElement' ).data( 'initial-value' );	
		}
		if ( $( this ).find( '.btQuoteItemInput > .btQuoteSelect' ).length > 0 ) {
			var oDropdown = $( this ).find( '.btQuoteSelect' ).data("dd");
			if ( oDropdown !== undefined ) {
				bt_cc_init_dropdown( oDropdown, "#"+$( this ).attr('id'), initial_value );	
			}	
		} else if ( $( this ).find( '.btQuoteItemInput > .btQuoteSlider' ).length > 0 ) {
			if ( $( this ).find( '.btQuoteItemInput > .btQuoteSlider' ).slider( 'instance' ) ) {
				bt_cc_set_slider_value( $( this ).find( '.btQuoteItemInput > .btQuoteSlider' ), initial_value );
			} else {
				/* W3 Tiotal cache safe path */
				setTimeout( bt_cc_set_slider_value, 2000, $( this ).find( '.btQuoteItemInput > .btQuoteSlider' ), initial_value, true );
			}
			
		} else if ( $( this ).find( '.btQuoteItemInput > .btQuoteText' ).length > 0 ) {
			$( this ).find( '.btQuoteText' ).val( initial_value );
		} else if ( $( this ).find( '.btQuoteItemInput > .btQuoteSwitch' ).length > 0 ) {
			if ( 
			( $( this ).find( '.btQuoteSwitch' ).data('on') == initial_value && !$( this ).find( '.btQuoteSwitch' ).hasClass('on') ) || 
			( $( this ).find( '.btQuoteSwitch' ).data('off') == initial_value && $( this ).find( '.btQuoteSwitch' ).hasClass('on') )
			) {
				$( this ).find( '.btQuoteSwitch' ).trigger('click');
			}
		}
		bt_cc_eval_conditions( initial_value, $( this ).find( '.btQuoteElement' ).data( 'condition' ) );
		bt_quote_total( $( this ).closest( '.btQuoteBooking' ) );
		bt_paypal_items( $( this ).closest( '.btQuoteBooking' ) );
		return $(this);
	}
	
	window.bt_cc_eval_conditions = function( val, conditions ){
		// alert(conditions);
		if ( conditions == '' || conditions == undefined ) return false;
		if ( val == '' ) val = 0;	

		if ( conditions.indexOf(';') == -1 ) {
			conditions = atob( conditions );
			var bt_cc_conditions = conditions.split(/\r?\n/);
		} else {
			var bt_cc_conditions = conditions.split("#bt_cc_nl#");
		}
		$.each( bt_cc_conditions, function( index, value ) {
			if ( bt_cc_conditions[index] != undefined ) {
				var bt_cc_single_condition = bt_cc_conditions[index].split(";");
				var eval_action  = bt_cc_single_condition[2];
				if ( bt_cc_single_condition[3] == 'lock' ) {
					eval_action += '.bt_cc_lock()';	
				} else if ( bt_cc_single_condition[3] == 'unlock' ) {
					eval_action += '.bt_cc_unlock()';	
				}
				if ( bt_cc_single_condition[0] != '' ) {
					var eval_code =  'if ( ' + val + decodeURIComponent( bt_cc_single_condition[0] ) + ' ) { $(\'#' + bt_cc_single_condition[1] +  '\').' + eval_action + '; }' ;
					eval( eval_code );	
				}				
			}

		});
		return false;
	}

	
	// Paypal calculation
	
	window.bt_paypal_items = function( c ) {
					
		$( c ).each(function() {
		
			if ( $( this ).find( '.btPayPalButton' ).length > 0 ) {
				
				var form = $( this ).find( '.btPayPalButton' ).next();
				form.find( '.btPayPalItem' ).remove();
				
				var x = 0;
		
				// items not in multiply and group
				$( this ).find( '.btQuoteBookingForm' ).find( '.btQuoteItem' ).not( '.btQuoteMBlock .btQuoteItem' ).not( '.btQuoteGBlock .btQuoteItem' ).each(function() {
					
					var unit_price = 0;
					var val = 0;
					
					var selected_name = '';

					$( this ).find( '.btQuoteText' ).each(function() {
						unit_price = bt_parse_float( $( this ).data( 'price' ) );
						val = bt_parse_float( $( this ).val() );
					});
					
					$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
						unit_price = bt_parse_float( $( this ).data( 'value' ) );
						val = 1;
						selected_name = $( this ).find( '.ddlabel' )[0].innerHTML;
						if ( $( this ).is( ':first-child' ) ) {
							selected_name = '';
						}
					});
					
					var is_slider = false;
					$( this ).find( '.btQuoteSlider' ).each(function() {
						unit_price = bt_parse_float( $( this ).data( 'price' ) );
						val = bt_cc_get_slider_value ( $( this ) );
						is_slider = true;
					});
					
					$( this ).find( '.btQuoteSwitch' ).each(function() {
						if ( $( this ).hasClass( 'on' ) ) {
							unit_price = bt_parse_float( $( this ).data( 'on' ) );
						} else {
							unit_price = bt_parse_float( $( this ).data( 'off' ) );
						}
						val = 1;
					});

					var label = $( this ).find( 'label' ).html();
					
					if ( is_slider ) {
						label = label + ': ' + val;
					}
					
					if ( selected_name != '' ) {
						selected_name = selected_name.replace( '<span class="description">', '/' );
						selected_name = selected_name.replace( '</span>', '' );
						if ( label.endsWith( '?' ) || label.endsWith( ':' ) ) {
							label = label + ' ' + selected_name;
						} else {
							label = label + ': ' + selected_name;
						}
						
					}

					val = val * unit_price;
					
		
					if ( label !== undefined && val > 0 ) {
						x++;
						val = val.toFixed( 2 );
						form.append( '<input type="hidden" name="item_name_' + x + '" value="' + label + '" class="btPayPalItem"><input type="hidden" name="amount_' + x + '" value="' + val + '" class="btPayPalItem">' );
					}
				});
				
				
				// multiply
				
				$( this ).find( '.btQuoteBookingForm' ).find( '.btQuoteMBlock' ).each(function() {

					var m_total = 1;
					var m_first = true;
					var m_val = 0;
					var selected_name = '';
					var label = '';
					
					$( this ).find( '.btQuoteItem' ).each(function() {
				
						$( this ).find( '.btQuoteText' ).each(function() {
							var unit_price = bt_parse_float( $( this ).data( 'price' ) );
							var val = bt_parse_float( $( this ).val() );
							val = val * unit_price;
							if ( m_first ) {
								m_val = val;
								label = $( this ).closest( '.btQuoteItem' ).find( 'label' ).html();
							} else {
								m_total *= val;
							}
							m_first = false;
						});
						
						$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
							var val = bt_parse_float( $( this ).data( 'value' ) );
							if ( m_first ) {
								m_val = val;
								label = $( this ).closest( '.btQuoteItem' ).find( 'label' ).html();
								selected_name = $( this ).find( '.ddlabel' )[0].innerHTML;
								selected_name = selected_name.substring( 0, selected_name.indexOf( '<span' ) );
								if ( $( this ).is( ':first-child' ) ) {
									selected_name = '';
								}
								if ( selected_name != '' ) label = label + ': ' + selected_name;											
							} else {
								m_total *= val;
							}
							m_first = false;
						});
						
						$( this ).find( '.btQuoteSlider' ).each(function() {
							var unit_price = bt_parse_float( $( this ).data( 'price' ) );
							// var val = bt_parse_float( $( this ).slider( 'value' ) );
							var val = bt_cc_get_slider_value ( $( this ) );
							val = val * unit_price;
							if ( m_first ) {
								m_val = val;
								label = $( this ).closest( '.btQuoteItem' ).find( 'label' ).html();
							} else {
								m_total *= val;
							}
							m_first = false;
						});
						
						$( this ).find( '.btQuoteSwitch' ).each(function() {
							if ( $( this ).hasClass( 'on' ) ) {
								var val = bt_parse_float( $( this ).data( 'on' ) );
							} else {
								var val = bt_parse_float( $( this ).data( 'off' ) );
							}							
							if ( m_first ) {
								m_val = val;
								label = $( this ).closest( '.btQuoteItem' ).find( 'label' ).html();
							} else {
								m_total *= val;
							}
							m_first = false;
						});
						
						
					});
					
					if ( m_total > 0 && m_val > 0 ) {
						x++;
						m_val = m_val.toFixed( 2 );
						form.append( '<input type="hidden" name="item_name_' + x + '" value="' + label + '" class="btPayPalItem"><input type="hidden" name="amount_' + x + '" value="' + m_val + '" class="btPayPalItem"><input type="hidden" name="quantity_' + x + '" value="' + m_total + '" class="btPayPalItem">' );
					}

				});
				
				
				// group
				
				$( this ).find( '.btQuoteBookingForm' ).find( '.btQuoteGBlock' ).each(function() {
					
					var eval_code = $( this ).data( 'eval' );
					
					var paypal_label = $( this ).data( 'paypal_label' );
						
					var group_array = [];
					
					$( this ).find( '.btQuoteItem' ).each(function() {
						
						var val;
				
						$( this ).find( '.btQuoteText' ).each(function() {
							var unit_price = bt_parse_float( $( this ).data( 'price' ) );
							val = bt_parse_float( $( this ).val() );
							val = val * unit_price;
						});
						
						$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
							val = bt_parse_float( $( this ).data( 'value' ) );
						});
						
						$( this ).find( '.btQuoteSlider' ).each(function() {
							var unit_price = bt_parse_float( $( this ).data( 'price' ) );
							// val = bt_parse_float( $( this ).slider( 'value' ) );
							val = bt_cc_get_slider_value ( $( this ) );
							val = val * unit_price;
						});
						
						$( this ).find( '.btQuoteSwitch' ).each(function() {
							if ( $( this ).hasClass( 'on' ) ) {
								val = bt_parse_float( $( this ).data( 'on' ) );
							} else {
								val = bt_parse_float( $( this ).data( 'off' ) );
							}
						});
						
						group_array.push( val );
						
					});
					
					var patt = /\$\d+/igm;
					var match = eval_code.match( patt );

					if ( match === null ) {
						eval_code = atob( eval_code );
						match = eval_code.match( patt );
					}

					if ( match === null ) {
						return;
					}
					
					for ( var i = 0; i < match.length; i++ ) {
						eval_code = eval_code.replace( match[ i ], group_array[ i ] );
					}
					var g_total = eval( '(function() {' + decodeURIComponent(eval_code) + '}())' );
					
					if ( paypal_label != '' && g_total > 0 ) {
						x++;
						g_total = g_total.toFixed( 2 );
						form.append( '<input type="hidden" name="item_name_' + x + '" value="' + paypal_label + '" class="btPayPalItem"><input type="hidden" name="amount_' + x + '" value="' + g_total + '" class="btPayPalItem"><input type="hidden" name="quantity_' + x + '" value="1" class="btPayPalItem">' );
					}

				});
			}
		});
	}
	
	// Currency format
	
	window.bt_cc_currency_format = function( c, n ) {
		var regex = /(\d)(?=(\d{3})+\.)/g;
		var regex_replace = '$1,';
		
		var format = c.data( 'total_format' );
		var decimals = c.data( 'total_decimals' );

		if ( format == 'currency_1' || format == 'currency_2' || format == 'currency_3' ) {
			
			n = n.toFixed( decimals );
			
			if ( format == 'currency_2' || format == 'currency_3' ) {
				n = n.replace( '.', ',' );
				regex = /(\d)(?=(\d{3})+\,)/g;
				if ( format == 'currency_2' ) {
					regex_replace = '$1.';
				} else if ( format == 'currency_3' ) {
					regex_replace = '$1 ';
				}
			}
			
			n = n.replace( regex, regex_replace );
			
		} else if ( format == 'decimal_1' || format == 'decimal_2' ) {
			
			n = parseFloat( n.toFixed( decimals ) ).toString();
			if ( format == 'decimal_2' ) {
				n = n.replace( '.', ',' );
			}
			
		} else if ( format == 'rounded' ) {
			
			n = Math.round( n );
			
		}
		
		return n;
	}
	
	// Total calculation
	
	window.bt_quote_total = function( c ) {
		
		var c = $( c );
	
		total = 0;
		

		c.find( '.btQuoteText' ).not( '.btQuoteMBlock .btQuoteText' ).not( '.btQuoteGBlock .btQuoteText' ).each(function() {
			var unit_price = bt_parse_float( $( this ).data( 'price' ) );
			var val = bt_parse_float( $( this ).val() );
			val = val * unit_price;
			total += val;
		});
		
		c.find( '.btQuoteSelect' ).not( '.btQuoteMBlock .btQuoteSelect' ).not( '.btQuoteGBlock .btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
			var val = bt_parse_float( $( this ).data( 'value' ) );
			total += val;
		});
		
		c.find( '.btQuoteSlider' ).not( '.btQuoteMBlock .btQuoteSlider' ).not( '.btQuoteGBlock .btQuoteSlider' ).each(function() {
			var unit_price = bt_parse_float( $( this ).data( 'price' ) );
			var offset = bt_parse_float( $( this ).data( 'offset' ) );
			var val = bt_cc_get_slider_value ( $( this ) );
			val = val * unit_price;
			total += val;
			if ( ! $( this ).closest( '.btQuoteBooking' ).find( '.btPayPalButton' ).length > 0 ) {
				total += offset;
			}
		});
		
		c.find( '.btQuoteSwitch' ).not( '.btQuoteMBlock .btQuoteSwitch' ).not( '.btQuoteGBlock .btQuoteSwitch' ).each(function() {
			if ( $( this ).hasClass( 'on' ) ) {
				total += bt_parse_float( $( this ).data( 'on' ) );
			} else {
				total += bt_parse_float( $( this ).data( 'off' ) );
			}
		});
							
		
		// multiply
		
		c.find( '.btQuoteMBlock' ).each(function() {
								
			var m_total = 0;
			var m_first = true;
			$( this ).find( '.btQuoteText' ).each(function() {
									   
				var unit_price = bt_parse_float( $( this ).data( 'price' ) );
				var val = bt_parse_float( $( this ).val() );
				val = val * unit_price;
				if ( m_first ) {
					m_total = val;
				} else {
					m_total *= val;
				}
				m_first = false;
											 
			});
			
			$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
										
										  
				var val = bt_parse_float( $( this ).data( 'value' ) );
										   
				if ( m_first ) {
					m_total = val;
				} else {
					m_total *= val;
				}
				m_first = false;
											
			});
			
			$( this ).find( '.btQuoteSlider' ).each(function() {
									   
				var unit_price = bt_parse_float( $( this ).data( 'price' ) );
				var offset = bt_parse_float( $( this ).data( 'offset' ) );
				//var val = bt_parse_float( $( this ).slider( 'value' ) );
				var val = bt_cc_get_slider_value ( $( this ) );
				val = val * unit_price;
				if ( m_first ) {
					m_total = val;
				} else {
					m_total *= val;
				}
				m_total += offset;
				m_first = false;
										   
			});
			
			$( this ).find( '.btQuoteSwitch' ).each(function() {
									   
				if ( $( this ).hasClass( 'on' ) ) {
					var val = bt_parse_float( $( this ).data( 'on' ) );
				} else {
					var val = bt_parse_float( $( this ).data( 'off' ) );
				}							
				if ( m_first ) {
					m_total = val;
				} else {
					m_total *= val;
				}
				m_first = false;
											
			});
									
			
			total += m_total;
			
		});
							
						  
		
		// group
		
		c.find( '.btQuoteGBlock' ).each(function() {
			
			var eval_code = $( this ).data( 'eval' );
			
			var group_array = [];
			
			$( this ).find( '.btQuoteItem .btQuoteItemInput' ).each(function() {
				
				var val = 0;
				
				$( this ).find( '.btQuoteText' ).each(function() {
					var unit_price = bt_parse_float( $( this ).data( 'price' ) );
					val = bt_parse_float( $( this ).val() );
					val = val * unit_price;
				});
				
				$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {							
					val = bt_parse_float( $( this ).data( 'value' ) );	
				});
				
				$( this ).find( '.btQuoteSlider' ).each(function() {
					var unit_price = bt_parse_float( $( this ).data( 'price' ) );
					var offset = bt_parse_float( $( this ).data( 'offset' ) );
					val = bt_cc_get_slider_value( $( this ) );
					val = val * unit_price;
				});
				
				$( this ).find( '.btQuoteSwitch' ).each(function() {
					if ( $( this ).hasClass( 'on' ) ) {
						val = bt_parse_float( $( this ).data( 'on' ) );
					} else {
						val = bt_parse_float( $( this ).data( 'off' ) );
					}
				});
				
				group_array.push( val );

			});

			var patt = /\$\d+/igm;
			var match = eval_code.match( patt );

			if ( match === null ) {
				eval_code = atob( eval_code );
				match = eval_code.match( patt );
			}

			if ( match === null ) {
				return;
			}

			for ( var i = 0; i < match.length; i++ ) {
				eval_code = eval_code.replace( match[ i ], group_array[ i ] );
			}
			
			var g_total = eval( '(function() {' + decodeURIComponent( eval_code ) + '}())' );
			
			total += g_total;
			
		});
		
		total = bt_cc_currency_format( c, total );

		c.find( '.btQuoteTotalCalc' ).html( total );
		c.data( 'total', total );
	}

	// Init functions
	
	// Dropdown init

	window.bt_cc_init_dropdown = function( elem, id, index ) {
		elem.set( "selectedIndex", index );
		var ui2 = $( id ).find( '._msddli_.selected' );
		var val = elem.getData().data.value;
		ui2.data( 'value', val );
		bt_cc_eval_conditions( val, ui2.closest( '.btQuoteSelect' ).data( 'condition' ) );
		bt_quote_total( ui2.closest( '.btQuoteBooking' ) );
		bt_paypal_items( ui2.closest( '.btQuoteBooking' ) );
	}
	
	// Base init
	
	window.bt_cc_init = function( ) {
		
		$( ".btQuoteBooking" ).each(function( index ) {
			var c = $( this );

			
			
			// Init elements 
			
			c.find( '.btContactDate' ).datepicker({
				prevText: window.bt_cc_translate['prev'],
				nextText: window.bt_cc_translate['next'],
				dateFormat: c.data( 'date_format' ),
				dayNamesMin: [ window.bt_cc_translate['su'], window.bt_cc_translate['mo'], window.bt_cc_translate['tu'], window.bt_cc_translate['we'], window.bt_cc_translate['th'], window.bt_cc_translate['fr'], window.bt_cc_translate['sa'] ],
				monthNames: [ window.bt_cc_translate['january'], window.bt_cc_translate['february'], window.bt_cc_translate['march'], window.bt_cc_translate['april'], window.bt_cc_translate['may'], window.bt_cc_translate['june'], window.bt_cc_translate['july'], window.bt_cc_translate['august'], window.bt_cc_translate['september'], window.bt_cc_translate['october'], window.bt_cc_translate['november'], window.bt_cc_translate['december'] ],
				beforeShow: function( input, inst ) {
					$( '.ui-datepicker' ).addClass( 'btDatePicker' );
				}
			});
			// console.log(window.is_rtl);
			// console.log(window.rtl_direction);
			c.find( '.btQuoteSlider' ).each(function() {
				$( this ).slider({
					min: ! window.is_rtl ? $( this ).data( 'min' ) : -$( this ).data( 'max' ),
					max: ! window.is_rtl ? $( this ).data( 'max' ) : -$( this ).data( 'min' ),
					step: $( this ).data( 'step' ),
					value: ! window.is_rtl ? $( this ).data( 'value' ) : -$( this ).data( 'value' ),
					change: function( event, ui ) { 
						// console.log( 'ui.value change: ' + ui.value );
						$( this ).find('.btQuoteSliderValue').html( window.rtl_direction * $( this ).slider( 'value' ) );
						bt_cc_eval_conditions( window.rtl_direction * $( this ).slider( 'value' ), $( this ).data( 'condition' ) );
					}
				});
				bt_cc_eval_conditions( window.rtl_direction * $( this ).data( 'value' ), $( this ).data( 'condition' ) );
			});
			
			c.find( '.ui-slider-handle' ).each(function() {
				$( this ).append( $( this ).closest( '.btQuoteItemInput' ).find( $( '.btQuoteSliderValue' ) ) );
			});
			

			c.find( '.btQuoteSlider' ).each(function() {
				var this_slider = $( this );
				$( this ).slider({
					slide: function( event, ui ) {
						var val = window.rtl_direction * ui.value; 
						// console.log( 'ui.value slide: ' + ui.value );
						// console.log( 'val slide: ' + val );
						this_slider.slider( 'value', val );
						this_slider.find('.btQuoteSliderValue').html( val );
						bt_quote_total( c );
						bt_paypal_items( c );
					}
				});
			});			

			
			c.find( '.btQuoteSwitch' ).on( 'click', function() {
				if ( $( this ).hasClass( 'on' ) ) { 
					$( this ).removeClass( 'on' );
					bt_cc_eval_conditions( $( this ).data( 'off' ), $( this ).data( 'condition' ) );
				} else {
					$( this ).addClass( 'on' );
					bt_cc_eval_conditions( $( this ).data( 'on' ), $( this ).data( 'condition' ) );
				}
				bt_quote_total( c );
				bt_paypal_items( c );
			});
			
			c.find( '.btQuoteSwitch' ).each( function() {
				if ( $( this ).hasClass( 'on' ) ) { 
					bt_cc_eval_conditions( $( this ).data( 'on' ), $( this ).data( 'condition' ) );
				} else {
					bt_cc_eval_conditions( $( this ).data( 'off' ), $( this ).data( 'condition' ) );
				}
			});
			
			c.find( '.btPayPalButton' ).on( 'click', function() {
				var data = {
					'action': 'bt_cc_paypal',
					'total' : c.data( 'total' ),
					'name' : c.find( '.btContactName' ).val(),
					'email' : c.find( '.btContactEmail' ).val(),
					'phone' : c.find( '.btContactPhone' ).val(),
					'address' : c.find( '.btContactAddress' ).val(),
					'message' : c.find( '.btContactMessage' ).val(),
				};

				jQuery('.btPayPalForm .form_amount').val(c.data( 'total' ));

				$.ajax({
					type: 'POST',
					url: c.data( 'url_ajax' ),
					data: data,
					async: true,
					success: function( response ) {
						console.log(response);
						if(response){
							jQuery( '.btPayPalForm' ).submit();
						}
					},
					error: function( xhr, status, error ) {
						c.find( '.btSubmitMessage' ).hide().html( c.data( 'message_error' ) ).fadeIn();
					}
				});
			});
			
			c.find( '.btQuoteText' ).each(function() {
				bt_cc_eval_conditions( $( this ).val(), $( this ).data( 'condition' ) );
			});
			
			c.find( '.btQuoteText' ).keyup(function() {
				bt_quote_total( c );
				bt_paypal_items( c );
				bt_cc_eval_conditions( $( this ).val(), $( this ).data( 'condition' ) );
			});
			
			c.find( '.btContactNext' ).click(function() {
				$( 'html, body' ).delay( 1000 ).animate({
					scrollTop: ( $( this ).closest( '.btQuoteBooking' ).find( '.btTotalQuoteContactGroup' ).offset().top - 30 )
				}, 400 );
				
				var contact_group = $( this ).closest( '.btQuoteBooking' ).find( '.btTotalQuoteContactGroup' );
				
				$( this ).closest( '.btQuoteBooking' ).find( '.btTotalQuoteContactGroup' ).addClass( 'btActive' );
				$( this ).closest( '.btQuoteBooking' ).find( '.btQuoteBookingForm' ).removeClass( 'btActive' );
			});

			c.find( '.btContactSubmit' ).click(function() {

				c.find( '.btContactFieldError' ).removeClass( 'btContactFieldError' );
		
				var val = true;
				
				c.find( '.btContactField' ).each(function() {
					if ( $( this ).parent().hasClass( 'btContactFieldMandatory' ) 
					&& ( ( $( this ).val() == '' && ! $( this ).hasClass( 'btContactTime' ) ) 
					|| ( $( this ).hasClass( 'btContactTime' ) && $( this ).hasClass( 'btNotSelected' ) ) ) ) {
						$( this ).parent().addClass( 'btContactFieldError' );
						val = false;
					}
				});
				
				if ( ! val ) {
					c.find( '.btSubmitMessage' ).hide().html( c.data( 'message_mandatory' ) ).fadeIn();
					return false;
				}

				var quote = '';
				var back = 0;
				var bt_is_odd = function( n ) {
					return ( n % 2 ) == 1;
				}

				c.find( '.btQuoteItem' ).each(function() {
					
					back++;
					var item_val = 0;
					var selected_name = '';
					
					$( this ).find( '.btQuoteText' ).each(function() {
						item_val = bt_parse_float( $( this ).val() );
					});
					
					$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
						selected_name = $( this ).find( '.ddlabel' )[0].innerHTML;
						if ( $( this ).is( ':first-child' ) ) {
							selected_name = '';
						}
						item_val = bt_parse_float( $( this ).data( 'value' ) );
					});
					
					var is_slider = false;
					var slider_val;
					$( this ).find( '.btQuoteSlider' ).each(function() {
						var unit_price = bt_parse_float( $( this ).data( 'price' ) );
						// slider_val = bt_parse_float( $( this ).slider( 'value' ) );
						slider_val = bt_cc_get_slider_value ( $( this ) );
						item_val = slider_val * unit_price;
						is_slider = true;
					});

					$( this ).find( '.btQuoteSwitch' ).each(function() {
						if ( $( this ).hasClass( 'on' ) ) {
							item_val = bt_parse_float( $( this ).data( 'on' ) );
						} else {
							item_val = bt_parse_float( $( this ).data( 'off' ) );
						}
					});
					
					var label = $( this ).find( 'label' ).html();
					
					if ( is_slider ) {
						label = label + ': ' + slider_val;
					}						
					
					if ( selected_name != '' ) {
						selected_name = selected_name.replace( '<span class="description">', '/' );
						selected_name = selected_name.replace( '</span>', '' );
						label = label + ': ' + selected_name;
					}
					
					var background = '';
					if ( bt_is_odd( back ) ) background = ' ' + 'style="background:#eee;"';
					
					item_val = bt_cc_currency_format( c, item_val );
					
					if ( label !== undefined && label !== null ) {
						quote += encodeURI( '<tr' + background + '>\r\n<td style="padding:.5em;">' + label + '</td>\r\n<td style="text-align:right;padding:.5em;">' + item_val + '</td>\r\n</tr>' ) + "\r\n";
					}
					
				});


				var quote_simple = [];
				var total_quote = 0;
				var quote_simple_count = 0;
				c.find( '.btQuoteGBlock .btQuoteItem' ).each(function() {
					quote_simple_inside = [];
					quote_simple_inside[0] = $( this ).find('label').text();
					if( quote_simple_inside[0] == 'Destination City:' ){
						quote_simple_inside[1] = $( this ).find('.btQuoteItemInput .ddTitleText .ddlabel').text();
					}else{
						quote_simple_inside[1] = $( this ).find('.btQuoteSlider .btQuoteSliderValue').text();
						total_quote = Number(total_quote) + Number(quote_simple_inside[1]);
					}
					quote_simple[quote_simple_count] = quote_simple_inside;
					quote_simple_count++;
				});

				console.log(quote_simple);
				console.log(total_quote);

				var recaptcha_response = '';
				if ( typeof grecaptcha !== 'undefined' ) {
					var recaptcha_response = grecaptcha.getResponse( c.find( '.g-rec' ).data( 'widget_id' ) );
					grecaptcha.reset( c.find( '.g-rec' ).data( 'widget_id' ) );
				}
				
				var email_confirmation = 'no';
				if ( c.find( '.bt_cc_email_confirmation' ).length ) {
					if ( c.find( '.bt_cc_email_confirmation' ).prop( 'checked' ) ) {
						email_confirmation = 'yes';
					}
				} else {
					email_confirmation = 'yes';
				}

				var email_gdpr = 'no';
				if ( c.find( '.bt_cc_email_gdpr' ).length ) {
					if ( c.find( '.bt_cc_email_gdpr' ).prop( 'checked' ) ) {
						email_gdpr = 'yes';
					}
				} else {
					email_gdpr = 'yes';
				}

				if ( email_gdpr == 'no' ) {
					c.find( '.btSubmitMessage' ).html(c.data( 'email_gdpr_not_text' ) );
					return false;
				}

				var data = {
					'action': 'bt_cc',
					'recaptcha_response': recaptcha_response,
					'recaptcha_secret': c.data( 'rec_secret_key' ),
					'admin_email': c.data( 'admin_email' ),
					'email_client': c.data( 'email_client' ),
					'currency': c.data( 'currency' ),
					'currency_after': c.data( 'currency_after' ),
					'currency_space': c.data( 'currency_space' ),
					'email_confirmation': email_confirmation,
					'url_confirmation': c.data( 'url_confirmation' ),
					'subject': c.data( 'subject' ),
					'email_header': c.data( 'email_header' ),
					'email_footer': c.data( 'email_footer' ),
					'quote' : quote,
					'quote_simple' : quote_simple,
					'total_quote' : total_quote,
					'total' : c.data( 'total' ),
					'total_text' : c.data( 'total_text' ),
					'RequestedCollectionDate': c.find( '.RequestedCollectionDate' ).val(),
					'CollectionsName': c.find( '.CollectionsName' ).val(),
					'CollectionsEmail': c.find( '.CollectionsEmail' ).val(),
					'name' : c.find( '.btContactName' ).val(),
					'email' : c.find( '.btContactEmail' ).val(),
					'phone' : c.find( '.btContactPhone' ).val(),
					'address' : c.find( '.btContactAddress' ).val(),
					'date' : c.find( '.btContactDate' ).val(),
					'time' : c.find( '.btContactTime option:selected' ).val(),
					'date_text': c.data( 'date_text' ),
					'time_text': c.data( 'time_text' ),
					'message' : c.find( '.btContactMessage' ).val(),
					'bt_cc_nonce': c.data( 'bt_cc_nonce' ),
					'email_gdpr': c.data( 'email_gdpr' ),
					'email_gdpr_text': c.data( 'email_gdpr_text' ),
					'email_gdpr_not_text': c.data( 'email_gdpr_not_text' )
				};
				
				c.find( '.btSubmitMessage' ).hide().html( c.data( 'message_please_wait' ) ).fadeIn();

				$.ajax({
					type: 'POST',
					url: c.data( 'url_ajax' ),
					data: data,
					async: true,
					success: function( response ) {
						//response = $.trim( response );
						alert(response);
						//if ( response == 'ok' ) {
						//	//alert(1);
						//	if ( c.data( 'url_confirmation' ) == '' || c.data( 'url_confirmation' ) == null ) {
						//		c.find( '.btSubmitMessage' ).hide().html( c.data( 'message_success' ) ).fadeIn();
						//	} else {
						//		window.location = c.data( 'url_confirmation' );
						//	}
                        //
						//} else {
						//	//alert(2);
						//	console.log(c.data( 'url_ajax' ));
						//	c.find( '.btSubmitMessage' ).hide().html( c.data( 'message_error' ) ).fadeIn();
						//}
					},
					error: function( xhr, status, error ) {
						c.find( '.btSubmitMessage' ).hide().html( c.data( 'message_error' ) ).fadeIn();
					}
				});
				
				return false;
			
			});
			
			// CF 7 support
			
			c.find( '.wpcf7-submit' ).click(function() {

	
				var val = true;
				var quote = '';

				c.find( '.btQuoteItem' ).each(function() {
					var item_val = 0;
					var selected_name = '';
					
					$( this ).find( '.btQuoteText' ).each(function() {
						item_val = bt_parse_float( $( this ).val() );
					});
					
					$( this ).find( '.btQuoteSelect' ).find( '._msddli_.selected' ).each(function() {
						selected_name = $( this ).find( '.ddlabel' )[0].innerHTML;
						if ( $( this ).is( ':first-child' ) ) {
							selected_name = '';
						}
						item_val = bt_parse_float( $( this ).data( 'value' ) );
					});
					
					var is_slider = false;
					var slider_val;
					$( this ).find( '.btQuoteSlider' ).each(function() {
						var unit_price = bt_parse_float( $( this ).data( 'price' ) );
						// slider_val = bt_parse_float( $( this ).slider( 'value' ) );
						slider_val = bt_cc_get_slider_value ( $( this ) );
						item_val = slider_val * unit_price;
						is_slider = true;
					});

					$( this ).find( '.btQuoteSwitch' ).each(function() {
						if ( $( this ).hasClass( 'on' ) ) {
							item_val = bt_parse_float( $( this ).data( 'on' ) );
						} else {
							item_val = bt_parse_float( $( this ).data( 'off' ) );
						}
					});
					
					var label = $( this ).find( 'label' ).html();
					
					if ( is_slider ) {
						label = label + ' (' + slider_val + ') ' ;
					}						
					
					if ( selected_name != '' ) {
						selected_name = selected_name.replace( '<span class="description">', '/' );
						selected_name = selected_name.replace( '</span>', '' );
						label = label + ' (' + selected_name + ') ';
					}
						
					item_val = bt_cc_currency_format( c, item_val );
					
					if ( label !== undefined && label !== null ) {
						quote += label + ': ' + item_val + "\r\n";
					}
					
				});
				
				quote += c.data( 'total_text' ) + ': ' + c.data( 'total' ) + "\r\n";
				
				c.find( 'input[name=bt-cc-data]' ).val( quote );

			});
			
				
			
			var total = 0;
			total = total.toFixed( 2 );
			bt_quote_total( c );
			bt_paypal_items( c );
		});	


		
	}
	
	// ---------------
	// Functions end
	// ---------------

	// Init
	
	var bt_cc_init_finished = false;
	
	document.addEventListener( 'readystatechange', function() { 
		if ( ! bt_cc_init_finished && ( document.readyState === 'interactive' || document.readyState === 'complete' ) ) {
			
			// base variables
			window.is_rtl = $( 'body' ).hasClass( 'rtl' );
			window.rtl_direction = !window.is_rtl ? 1 : -1;
			
			bt_cc_init();
			bt_cc_init_finished = true;
			
		}
	});
	
})( jQuery );