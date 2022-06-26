<?php

namespace Drupal\custom_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation;
use Drupal\Core\File\FileSystemInterface;


/**
 * Class RegistrationForm.
 */
class RegistrationForm extends FormBase
{

	/**
	 * {@inheritdoc}
	 */
	public function getFormId()
	{
		return 'custom_registration_form';
	}

	
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state)
	{
    // Adding the libraries to the form for fields validation.
    $form['#attached']['library'][] = 'custom_registration/jquery_validation';
    $form['#attached']['library'][] = 'custom_registration/custom_registration';
    // Unsetting the default HTML validation.
    $form['#attributes']['novalidate'] = 'novalidate';
		$form['first_name'] = array(
			'#title' => t('First Name'),
			'#type' => 'textfield',
			'#description' => 'Enter First Name',
			'#size' => 32,
			'#maxlength' => 128,
			'#required' => TRUE,
			);
		$form['last_name'] = array(
			'#title' => t('Last Name'),
			'#type' => 'textfield',
			'#description' => 'Enter Last Name',
			'#size' => 32,
			'#maxlength' => 128,
			'#required' => TRUE,
			);
		$form['email'] = array(
			'#title' => t('Email'),
			'#type' => 'email',
			'#description' => 'Enter Email ID (e.g: test@yahoo.com)',
			'#size' => 32,
			'#maxlength' => 128,
			'#required' => TRUE,
			);
    $form['phone'] = array(
      '#title' => t('Phone Number'),
      '#type' => 'number',
      '#description' => 'Enter 10 digit Contact Number (e.g: 1234567890)',
      '#size' => 32,
      '#maxlength' => 128,
      '#required' => TRUE,
      );
    $form['company'] = array(
      '#title' => t('Company Name'),
      '#type' => 'textfield',
      '#description' => 'Enter company name',
      '#size' => 32,
      '#maxlength' => 128,
      '#required' => TRUE,
      );
    $form['address'] = array(
      '#title' => t('Address'),
      '#type' => 'textarea',
      '#description' => 'Enter your full address',
      '#rows' => 4,
      '#resizable' => TRUE,
      '#required' => TRUE,
    );
    $form['message'] = array(
      '#title' => t('Contact Message'),
      '#type' => 'textarea',
      '#description' => 'Enter your message',
      '#rows' => 4,
      '#required' => TRUE,
    );
    $form['terms'] = array(
      '#title' => t('Accept terms and conditions'),
      '#type' => 'checkbox',
      '#required' => TRUE,
    );		
		$form['submit'] = [
			'#type' => 'submit',
			'#suffix' => $this->t('</div>'),
			'#value' => t('Register me'),
			'#attributes' => [
				'class' => ['submit btn btn-primary'],
			],
		];

		return $form;
	}

	

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get all values submitted.
		$fname = $form_state->getValue('first_name');
    $lname = $form_state->getValue('last_name');
    $email = $form_state->getValue('email');
    $phone = $form_state->getValue('phone');
    $company = $form_state->getValue('company');
    $address = $form_state->getValue('address');
    $message = $form_state->getValue('message');
    $terms = $form_state->getValue('terms');
    // Get User IP address.
    $ip_adddress = \Drupal::request()->getClientIp();
    // Insert the submission in the database.
		\Drupal::database()->insert('custom_registration')
		->fields([
		'first_name' => $fname,
		'last_name' => $lname,
    'email' => $email,
    'phone' => $phone,
    'company' => $company,
    'address' => $address,
    'message' => $message,
    'terms' => $terms,
    'ip_address' => $ip_adddress,
		])
		->execute();
    
    // Trigger mail with the submission values.
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'custom_registration';
    $key = 'general_mail';
    $to = \Drupal::config('system.site')->get('mail');
    $body_text = 'New Submission: <br />{submitted_value1}<br />{submitted_value2}<br />{submitted_value3}<br /><br />{submitted_value4}<br />{submitted_value5}<br />{submitted_value6}<br />{submitted_value7}<br />{submitted_value8}<br />';
    $body_text = str_replace('{submitted_value1}', $fname, $body_text);
    $body_text = str_replace('{submitted_value2}', $lname, $body_text);
    $body_text = str_replace('{submitted_value3}', $email, $body_text);
    $body_text = str_replace('{submitted_value4}', $phone, $body_text);
    $body_text = str_replace('{submitted_value5}', $company, $body_text);
    $body_text = str_replace('{submitted_value6}', $address, $body_text);
    $body_text = str_replace('{submitted_value7}', $message, $body_text);
    $body_text = str_replace('{submitted_value8}', $ip_adddress, $body_text);
    $params['body'] = $body_text;
    $params['subject'] = "Custom Registration Submission";
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Log the result to track the action of the mail event.
    if ($result['result'] !== true) {
      \Drupal::logger('custom_registration')->error('There was a problem sending your message and it was not sent.');
    }
    else {
      \Drupal::logger('custom_registration')->info('Your message has been sent.');
    }

    // Writing the submissions in the file and force downloading it.
    $namefile = $fname.".txt";
    $content = "New Submission:".$fname."</br>".$lname;
    
    //save file
    $file = fopen($namefile, "w") or die("Unable to open file!");
    fwrite($file, $content);
    fclose($file);
    
    //header download
    header("Content-Disposition: attachment; filename=\"" . $namefile . "\"");
    header("Content-Type: application/force-download");
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header("Content-Type: text/plain");
    echo $content;

    // Success message for data submission.
    \Drupal::messenger()->addMessage(t('Data submitted to the admin'));
	}
	
}
