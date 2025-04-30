<?php

return [
	'dateWidget' => '<div class="form-inline">' .
		'<div class="day">{{day}}</div><div class="month">{{month}}</div><div class="year">{{year}}</div>' .
		'<div class="hour">{{hour}}</div><div class="minute">{{minute}}</div>' .
		'<div class="second">{{second}}</div><div class="meridian">{{meridian}}</div></div>',
	'error' => '<div class="alert alert-danger" role="alert">{{content}}</div>',
	'success' => '<div class="alert alert-success" role="alert">{{content}}</div>',
	'label' => '<label{{attrs}} class="form-label">{{text}}{{tooltip}}</label>',
	'inputContainer' => '<div class="mb-3 {{type}}{{required}}">{{content}}{{help}}</div>',
	'datetimeContainer' => '<div class="mb-3 {{type}}{{required}}" role="group" ' .
		'aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
	'datetimeContainerError' =>
		'<div class="mb-3 {{formGroupPosition}}{{type}}{{required}} is-invalid" ' .
		'role="group" aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
	'inputContainerError' =>
		'<div class="mb-3 {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
		'{{content}}{{error}}{{help}}</div>',
	'checkboxContainer' => '<div class="mb-3 form-check {{type}}{{required}}">{{content}}{{help}}</div>',
	'checkboxLabel' => '<label id="{{groupId}}" class="form-check-label">{{text}}{{tooltip}}</label>',
	'checkboxContainerError' =>
		'<div class="form-check {{formGroupPosition}}{{type}}{{required}} is-invalid">' .
		'{{content}}{{error}}{{help}}</div>',
	'customCheckboxContainer' => '<div class="custom-control custom-checkbox ' .
		'{{type}}{{required}}">{{content}}{{help}}</div>',
	'customCheckboxContainerError' =>
		'<div class="custom-control custom-checkbox ' .
		'{{formGroupPosition}}{{type}}{{required}} is-invalid">{{content}}{{error}}{{help}}</div>',
	'checkboxInlineContainer' => '<div class="form-check form-check-inline {{type}}{{required}}">' .
		'{{content}}</div>',
	'checkboxInlineContainerError' => '<div class="form-check form-check-inline {{type}}{{required}} ' .
		'is-invalid">{{content}}</div>',
	'customCheckboxInlineContainer' => '<div class="custom-control custom-checkbox ' .
		'custom-control-inline {{type}}{{required}}">{{content}}</div>',
	'customCheckboxInlineContainerError' =>
		'<div class="custom-control custom-checkbox custom-control-inline ' .
		'{{formGroupPosition}}{{type}}{{required}} is-invalid">{{content}}</div>',
	'checkboxFormGroup' => '{{input}}{{label}}',
	'checkboxWrapper' => '<div class="form-check">{{label}}</div>',
	'checkboxInlineWrapper' => '<div class="form-check form-check-inline">{{label}}</div>',
	'customCheckboxWrapper' => '<div class="custom-control custom-checkbox">{{label}}</div>',
	'customCheckboxInlineWrapper' => '<div class="custom-control custom-checkbox custom-control-inline">' .
		'{{label}}</div>',
	'radioContainer' => '<div class="{{type}}{{required}}" role="group" ' .
		'aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
	'radioContainerError' =>
		'<div class="{{formGroupPosition}}{{type}}{{required}} is-invalid" role="group" ' .
		'aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
	'radioLabel' => '<label id="{{groupId}}" class="d-block">{{text}}{{tooltip}}</label>',
	'radioWrapper' => '<div class="form-check">{{hidden}}{{label}}</div>',
	'radioInlineWrapper' => '<div class="form-check form-check-inline">{{label}}</div>',
	'customRadioWrapper' => '<div class="custom-control custom-radio">{{hidden}}{{label}}</div>',
	'customRadioInlineWrapper' => '<div class="custom-control custom-radio custom-control-inline">' .
		'{{hidden}}{{label}}</div>',
	'staticControl' => '<p class="form-control-plaintext">{{content}}</p>',
	'inputGroupAddon' => '<div class="{{class}}">{{content}}</div>',
	'inputGroupContainer' => '<div{{attrs}}>{{prepend}}{{content}}{{append}}</div>',
	'inputGroupText' => '<span class="input-group-text">{{content}}</span>',
	'multicheckboxContainer' => '<div class="{{type}}{{required}}" role="group" ' .
		'aria-labelledby="{{groupId}}">{{content}}{{help}}</div>',
	'multicheckboxContainerError' =>
		'<div class="{{formGroupPosition}}{{type}}{{required}} is-invalid" role="group" ' .
		'aria-labelledby="{{groupId}}">{{content}}{{error}}{{help}}</div>',
	'multicheckboxLabel' => '<label id="{{groupId}}" class="d-block">{{text}}{{tooltip}}</label>',
	'multicheckboxWrapper' => '<fieldset class="form-group">{{content}}</fieldset>',
	'multicheckboxTitle' => '<legend class="col-form-label pt-0">{{text}}</legend>',
	'customFileLabel' => '<label class="custom-file-label"{{attrs}}>{{text}}{{tooltip}}</label>',
	'customFileFormGroup' => '<div class="custom-file {{invalid}}">{{input}}{{label}}</div>',
	'customFileInputGroupFormGroup' => '{{input}}',
	'customFileInputGroupContainer' =>
		'<div{{attrs}}>{{prepend}}<div class="custom-file {{invalid}}">{{content}}{{label}}</div>{{append}}</div>',
	'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}}>{{text}}{{tooltip}}</label>',
	'nestingLabelNestedInput' => '{{hidden}}<label{{attrs}}>{{input}}{{text}}{{tooltip}}</label>',
	// Used for button elements in button().
	'button' => '<button{{attrs}}>{{text}}</button>',
	// Used for checkboxes in checkbox() and multiCheckbox().
	'checkbox' => '<input type="checkbox" class="form-check-input" name="{{name}}" value="{{value}}"{{attrs}}>',
	// Container for error items.
	'errorList' => '<ul>{{content}}</ul>',
	// Error item wrapper.
	'errorItem' => '<li>{{text}}</li>',
	// File input used by file().
	'file' => '<input type="file" class="form-control" name="{{name}}"{{attrs}}>',
	// Fieldset element used by allControls().
	'fieldset' => '<fieldset{{attrs}}>{{content}}</fieldset>',
	// Open tag used by create().
	'formStart' => '<form{{attrs}}>',
	// Close tag used by end().
	'formEnd' => '</form>',
	// General grouping container for control(). Defines input/label ordering.
	'formGroup' => '{{label}}{{input}}',
	// Wrapper content used to hide other content.
	'hiddenBlock' => '<div style="display:none;">{{content}}</div>',
	// Generic input element.
	'input' => '<input type="{{type}}" class="form-control {{class}}" name="{{name}}"{{attrs}}/>',
	// Submit input element.
	'inputSubmit' => '<input type="{{type}}"{{attrs}}/>',
	'legend' => '<legend>{{text}}</legend>',
	// Option element used in select pickers.
	'option' => '<option value="{{value}}"{{attrs}}>{{text}}</option>',
	// Option group element used in select pickers.
	'optgroup' => '<optgroup label="{{label}}"{{attrs}}>{{content}}</optgroup>',
	// Select element,
	'select' => '<select name="{{name}}"{{attrs}} class="form-select">{{content}}</select>',
	// Multi-select element,
	'selectMultiple' => '<select name="{{name}}[]" multiple="multiple"{{attrs}}>{{content}}</select>',
	// Radio input element,
	'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}}>',
	// Textarea input element,
	'textarea' => '<textarea class="form-control {{class}}" name="{{name}}"{{attrs}}>{{value}}</textarea>',
	// Container for submit buttons.
	'submitContainer' => '<div class="submit">{{content}}</div>',
	// Confirm javascript template for postLink()
	'confirmJs' => '{{confirm}}',
	// selected class
	'selectedClass' => 'selected',
];
