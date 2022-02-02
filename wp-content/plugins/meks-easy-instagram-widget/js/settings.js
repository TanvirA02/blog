(function ($) {
	$(document).ready(function () {

		// OLD API AUTHORIZATION - IT HAS TO BE DELETE AFTER THE UPDATE
		var hash = window.location.hash;
		if ( hash.indexOf('access_token') > 0 && hash.indexOf('api_type') == -1 ) {
			var input = $('#meks-access-token');
			input.val(hash.split('=').pop());
			input.parents('form').find('#submit').click();
		}



		// NEW API HANDLING RETURNED TOKEN 
		var url_hash = window.location.hash;
		if ( url_hash.indexOf('access_token') > 0 && url_hash.indexOf('api_type') > 0 ) {

			var type_name = url_hash.split('=').pop();

			if ( 'business' !== type_name ) {
				set_personal_api_info( url_hash );
			}

			if ( 'business' == type_name ) {
				set_business_api_info( url_hash );
			}
		}



		// NEW API HANDLING AUTHORIZE BUTTON
		var instagram_button_connect = $('.meks-instagram-button-connect');
		var personal_api_url = instagram_button_connect.attr('data-personal-api');
		var	business_api_url = instagram_button_connect.attr('data-business-api');

		instagram_button_connect.on('click', function(e) {
			e.preventDefault();
	
			$('body').append(
				'<div class="meks-instagram-authorize-info">' +
					'<div class="meks-instagram-modal">' +
						'<p>Are you connecting a Personal or Business Instagram Profile?</p>' +
						'<div class="meks-instagram-switch-account-type">' +
							'<p>' +
								'<input type="radio" id="meks-instagram-personal-api" name="meks_instagram_account_type" value="personal" checked>' +
								'<label for="meks-instagram-personal-api"><strong>Personal</strong></label>' +
							'</p><p>' +
							'<input type="radio" id="meks-instagram-business-api" name="meks_instagram_account_type" value="business">' +
							'<label for="meks-instagram-business-api"><strong>Business</strong></label>' +
							'</p>' +
						'</div>' +
						'<a href="'+personal_api_url+'" class="meks-instagram-connect button button-primary">Connect</a>' +
						'<a href="JavaScript:void(0);"><i class="meks-instagram-modal-close fa fa-times"></i></a>' +
					'</div>' +
				'</div>'
			);
			
		});

		$('body').on('click', '.meks-instagram-modal-close', function(){
			$('.meks-instagram-authorize-info').remove();
		});

		$('body').on('change', 'input[name=meks_instagram_account_type]', function() {
            if ($('input[name=meks_instagram_account_type]:checked').val() === 'business') {
                $('a.meks-instagram-connect').attr('href', business_api_url);
            } else {
                $('a.meks-instagram-connect').attr('href', personal_api_url);
            }
		});

		$('body').on('change', 'input[name=meks_connected_account]', function() {
			var $this = $(this).next();
			$('#meks-access-token').val($this.data('access_token'));
			$('#meks-user-id').val($this.data('id'));
			$('#meks-api-type').val('business');
			$('body button.meks-connect-account').attr('data-id', $this.data('id')).attr('data-username', $this.data('username')).attr('data-name', $this.data('name')).attr('data-image', $this.data('image')).attr('data-access_token', $this.data('access_token'));			
		});

		$('body').on('click', 'button.meks-connect-account', function(){

			var $this = $(this);

			$.ajax({
				url: meks_js_settings.ajax_url,
				type: 'post',
				data: {
					action: 'meks_save_business_selected_account',
					user_id: $this.data('id'),
					username: $this.data('username'),
					name: $this.data('name'),
					image: $this.data('image'),
					access_token: $this.data('access_token'),
					nonce: meks_js_settings.nonce
				},
				success: function(response) {
					$('.meks-instagram-authorize-info').remove();
					$('#submit').click();
				}
			});
		});

	});


	/* 
		Function for Personal API to set token, user id, api type and token expiration time 
	*/
	function set_personal_api_info( data ) {

		var token = $('#meks-access-token');
		var user_id = $('#meks-user-id');
		var token_expires = $('#meks-token-expires-in');
		var api_type = $('#meks-api-type');

		var url_hash_arr = data.split('&');
	
		url_hash_arr.forEach( function( param ) {

			switch ( param.split('=').shift() ) {

				case '#access_token':
					token.val(param.split('=').pop());
					break;

				case 'expires_in':
					var expires_in_time = parseInt(param.split('=').pop()) + parseInt(Math.floor(Date.now() / 1000));
					token_expires.val(expires_in_time);
					break;

				case 'user_id':
					user_id.val(param.split('=').pop());
					break;

				case 'api_type':
					api_type.val(param.split('=').pop());
					type_name = param.split('=').pop();
					break;
			
				default:
					break;
			}
		});

		if( data.indexOf('expires_in') == -1 ) {
			var expires_in = parseInt( 86400 * 60 ) + parseInt(Math.floor(Date.now() / 1000));
			token_expires.val(expires_in);
		}

		token.parents('form').find('#submit').click();
	}

	/* 
		Function for Business API to pull Facebook Connected Accounts choose one and set it 
	*/
	function set_business_api_info( data ) {

		var token = '';
		var input_token_field = $('#meks-access-token');
		var url_hash_arr = data.split('&');

		if ( input_token_field.val() != '' ) {
			//return;
		}
	
		url_hash_arr.forEach( function( param ) {
			switch ( param.split('=').shift() ) {
				case '#access_token':
					token = param.split('=').pop();
					break;
				default:
					break;
			}
		});

		/* Set expires time for refresh token */
		var expires_in = parseInt( 86400 * 60 ) + parseInt(Math.floor(Date.now() / 1000));
		$('#meks-token-expires-in').val( expires_in );

		var svg_loader = $('<svg id="meks-loader" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="64px" height="64px" viewBox="0 0 128 128" xml:space="preserve"><rect x="0" y="0" width="100%" height="100%" fill="#f1f1f1" /><g><circle cx="16" cy="64" r="16" fill="#000000" fill-opacity="1"/><circle cx="16" cy="64" r="16" fill="#555555" fill-opacity="0.67" transform="rotate(45,64,64)"/><circle cx="16" cy="64" r="16" fill="#949494" fill-opacity="0.42" transform="rotate(90,64,64)"/><circle cx="16" cy="64" r="16" fill="#cccccc" fill-opacity="0.2" transform="rotate(135,64,64)"/><circle cx="16" cy="64" r="16" fill="#e1e1e1" fill-opacity="0.12" transform="rotate(180,64,64)"/><circle cx="16" cy="64" r="16" fill="#e1e1e1" fill-opacity="0.12" transform="rotate(225,64,64)"/><circle cx="16" cy="64" r="16" fill="#e1e1e1" fill-opacity="0.12" transform="rotate(270,64,64)"/><circle cx="16" cy="64" r="16" fill="#e1e1e1" fill-opacity="0.12" transform="rotate(315,64,64)"/><animateTransform attributeName="transform" type="rotate" values="0 64 64;315 64 64;270 64 64;225 64 64;180 64 64;135 64 64;90 64 64;45 64 64" calcMode="discrete" dur="720ms" repeatCount="indefinite"></animateTransform></g></svg>');

		$('body').css('opacity', '0.4');
		$('body').append(svg_loader);

		jQuery.ajax({
			url: meks_js_settings.ajax_url,
			type: 'post',
			data: {
				action: 'meks_save_token',
				access_token: token,
			},
			success: function (data) {
				var accounts = JSON.parse(data);
				//console.log(accounts);
				$('body').css('opacity', '1');
				svg_loader.remove();
				meks_set_accounts_details(accounts);
			}
		});

	}

	/* 
		Function create modal window and 
		display returned Business Instagram Accounts to allow user to pick one 
	*/
	function meks_set_accounts_details( accounts ) { 

		var modal_open = '<div class="meks-instagram-authorize-info"><div class="meks-instagram-modal meks-instagram-modal-business"><div class="meks-instagram-switch-account-type">';
		var modal_close = '</div><a href="JavaScript:void(0);"><i class="meks-instagram-modal-close fa fa-times"></i></a></div></div>';
		var html = '';
		var has_token = true;

		if ( accounts.length > 0 ) {

			accounts.forEach( function( account ) {

				if ( account.id == undefined || account.access_token == undefined ) {
					has_token = false;
					return;
				}
				
				var profile_picture_url = undefined !== account.profile_picture_url ? account.profile_picture_url : '';

				html += '<div class="meks-select-account"><input type="radio" id="meks-'+account.id+'" name="meks_connected_account"> ' +
						'<label class="meks-instagram-account" for="meks-'+account.id+'" data-id="'+account.id+'" data-name="'+account.name+'" data-username="'+account.username+'" data-image="'+profile_picture_url+'" data-access_token="'+account.access_token+'"> '+
							'<img height="50" width="50" src="'+profile_picture_url+'">'+
							'<strong>'+account.name+'</strong>'+
							'<div>@'+account.username+'</div>'+
							'<div> ('+account.id+') </div>'+
						'</label></div>';

							
			});
		}

		var info = accounts.length > 0 && has_token ? '<h3>Please select a Business profile</h3>' : '<h3>Something went wrong, please try authorization again!</h3><i>Note: be sure you have allowed correct Business Instagram account and Facebook Page connected to tha account on Facebook authorization window.<br> Only select one Page that is connected to your Business Instagram Account!<i>';
		var connect_button = accounts.length > 0 && has_token ? '<button class="meks-connect-account button button-primary">Connect Account</button>' : '';
		
		$('body').append(modal_open + info + html + connect_button + modal_close);

	}

})(jQuery);
