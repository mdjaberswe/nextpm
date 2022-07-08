/*! jquery-password-generator-plugin - v0.0.0 - 2015-10-23
* Copyright (c) 2015 Sergey Sokurenko; Licensed MIT */
(function ($) {
  $.passGen = function (options) {
    // Override default options with passed-in options
    options = $.extend({}, $.passGen.options, options);

    // Local varialbles declaration
    var charsets, charset = '', password = '', index;

    // Available character lists
    charsets = {
      'numeric'   : '0123456789',
      'lowercase' : 'abcdefghijklmnopqrstuvwxyz',
      'uppercase' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
      'special'   : '~!@#$%^&*()-+[]{}<>?'
    };

    // Defining merged character set
    $.each(charsets, function(key, value) {
      if (options[key]) {
        charset += value;
      }
    });

    // Generating the password
    for (var i=0; i< options.length; i++) {
      // defining random character index
      index = Math.floor(Math.random() * (charset.length));
      // adding the character to the password
      password += charset[index];
    }

    // Returning generated password value
    return password;
  };

  // Default options
  $.passGen.options = {
    'length' : 10,
    'numeric' : true,
    'lowercase' : true,
    'uppercase' : true,
    'special'   : false
  };
}(jQuery));