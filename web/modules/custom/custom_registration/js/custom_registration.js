/**
 * @file
 * JavaScript file for the Custom Registration module.
 */
(function ($, Drupal, drupalSettings) {

  // Alphabets only regex.
  jQuery.validator.addMethod("lettersonly", function(value, element) {
    return this.optional(element) || /^[a-z]+$/i.test(value);
  }, "Letters only please"); 

  // Phone number validation regex.
  jQuery.validator.addMethod("phoneUS", function(phone_number, element) {
    phone_number = phone_number.replace(/\s+/g, "");
    return this.optional(element) || phone_number.length > 9 && 
    phone_number.match(/^(\+?1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
  }, "Please specify a valid phone number");

  // Form fields validation begins.
  $(function() {
    $('form').validate({
      rules: {
        first_name: {
          required: true,
          lettersonly: true
        },
        last_name: {
          required: true,
          lettersonly: true
        },
        email: {
          required: true,
          email: true
        },
        phone: {
          required: true,
          phoneUS: true,
        },
        company: 'required',
        address: {
          required: true,
          maxlength: 250,
        },
        message: 'required',
        terms: 'required',
      },
      messages: {
        first_name: {
          required: 'This field is required',
        },
        last_name: {
          required: 'This field is required',
        },
        email: {
          required: 'This field is required',
          email: 'Please enter a valid email address',
        },
        phone: {
          required: 'This field is required',
        },
        company: 'This field is required',
        address: {
          required: 'This field is required',
          maxlength: 'Max length is 250 characters only.',
        },
        message: 'This field is required',
        terms: 'This field is required',
     },
     submitHandler: function(form) {
      form.submit();
    }

    });

  });

})(jQuery, Drupal, drupalSettings);