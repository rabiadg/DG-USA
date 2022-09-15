$(".input-select").click(function(){
  setTimeout(function() { 
     $('.input-select').removeClass("error");
}, 10);
 });


jQuery.validator.addMethod("lettersonly", function(value, element) {
  return this.optional(element) || /^[a-z][a-z\s]*$/i.test(value);
}, "Letters only please");

jQuery.validator.addMethod("Email", function(value, element) {
  return this.optional(element) || /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i.test(value);
}, "Please enter valid email");

jQuery.validator.addMethod("number_validate", function(value, element) {
  return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$/i.test(value);
}, "Please enter valid number");

jQuery.validator.addMethod("url_validate", function(value, element) {
  return this.optional(element) || /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i.test(value);
}, "Please enter valid URL");

jQuery.validator.addMethod("captcha_validate", function(value, element) {
  return this.optional(element) || ValidCaptcha();
}, "Please enter valid Captcha");


jQuery.validator.addMethod("captcha_validate2", function(value, element) {
  return this.optional(element) || ValidCaptcha2();
}, "Please enter valid Captcha");




  $("#ContactForm").validate({
    rules: {
      name: {

        required: true,
        lettersonly: true
      },
      email: {

        required: true,
        Email: true
      },

      number: {
        required: true,
        number_validate: true,
        minlength: 7,
        maxlength: 15,
        
      },



    },
 

    showErrors: function(errorMap, errorList) {
      this.defaultShowErrors();
      $('input').removeClass('error');
      $('.input-select').removeClass("error");
    },
  
  
    errorPlacement : function( error, element ) {
      if ( element.hasClass( 'input-select' )) {
        element.after( error );
        element.removeClass('error');
      }
      else {
      element.after( error ); // default error placement
      }
      },

    submitHandler: function(form) {
      jQuery(form).find('#body-loader-contact').show()
      jQuery(form).find('#submit-button-contact').hide() 

      $.ajax({
        type: 'post',
        url: '/get_proposal.php',
        data: $(form).serialize(),
        success: function(response) {
          console.log(response);
          jQuery(form).find('#body-loader-contact').hide()
         window.location.replace("/thank-you");


        }
      });
    }

  });


$("#getquoteform").validate({
  rules: {
    fullname: {
      required: true,
      lettersonly: true
    },

    company: {
      lettersonly: true
    },
    email: {
      required: true,
      email: true
    },
    phone: {
      required: true,
      number_validate: true,
      minlength: 7,
      maxlength: 15,
    },
    interest: {
      required: true
    },
    budget: {
      required: true
    },

  

  },

  onfocusout: function(el) {
    if (!this.checkable(el)){
        this.element(el);
    }
},

  showErrors: function(errorMap, errorList) {
    this.defaultShowErrors();
    $('input').removeClass('error');
    $('.input-select').removeClass("error");
  },


  errorPlacement : function( error, element ) {
    if ( element.hasClass( 'input-select' )) {
      element.after( error );
      element.removeClass('error');
    }
    else {
    element.after( error ); // default error placement
    }
    },

  submitHandler: function(form) {

    jQuery(form).find('#body-loader-getquote').show();
    jQuery(form).find('#submit-button-getquote').hide();

    $.ajax({
      type: 'post',
      url: '/mail_popup_getquote.php',
      data: $(form).serialize(),
      success: function(response) {
        // console.log(response);
        // jQuery(form).find('#body-loader-getquote').hide()
        //jQuery(form).find('#submit-button-getquote').show()
        //jQuery(form).find('#body-getquote-thankyou').show()
        window.location.replace("/thank-you");
        //jQuery(form).find('input:not(#submit-button-getquote),textarea').val('')
      }
    });
  }
  
});