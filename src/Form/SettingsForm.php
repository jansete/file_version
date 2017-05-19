<?php

namespace Drupal\file_version\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file_version\FileVersionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SettingsForm
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\file_version\FileVersionInterface
   */
  private $fileVersion;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\file_version\FileVersionInterface  $file_version
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileVersionInterface $file_version) {
    parent::__construct($config_factory);

    $this->fileVersion = $file_version;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('config.factory'),
      $container->get('file_version')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'file_version_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'file_version.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('file_version.settings');

    $form['enable_image_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable File Version for image styles'),
      '#default_value' => $config->get('enable_image_styles'),
      '#description' => $this->t('This option add simple token to image styles url.'),
    ];

    $form['enable_all_files'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable File Version for all files'),
      '#default_value' => $config->get('enable_all_files'),
      '#description' => $this->t('This option add simple token to all files url (including image styles).'),
    ];

    $random_token = $this->fileVersion->getCryptedToken('randomToken');
    $get_parameter_name = $config->get('get_parameter_name');

    $form['get_parameter_name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Get parameter name'),
      '#default_value' => $get_parameter_name,
      '#description'   => $this->t(
        'The name of the GET url parameter for the file version token. Eg: @get_parameter_name for @get_parameter_name=@random_token.',
        [
          '@get_parameter_name' => $get_parameter_name,
          '@random_token' => $random_token,
        ]
      ),
      '#maxlength' => 10,
      '#required' => TRUE,
    ];

    $form['extensions_blacklist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Extensions blacklist'),
      '#default_value' => $config->get('extensions_blacklist'),
      '#rows' => 5,
      '#description' => $this->t('Comma separated extensions to exclude. Must be different than whitelist extensions. Eg: png, jpeg, svg'),
    ];

    $form['extensions_whitelist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Extensions whitelist'),
      '#default_value' => $config->get('extensions_whitelist'),
      '#rows' => 5,
      '#description' => $this->t('Extensions to include. IMPORTANT: Not implemented yet.'),
      '#disabled' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('file_version.settings')
         ->set('enable_image_styles', $values['enable_image_styles'])
         ->set('enable_all_files', $values['enable_all_files'])
         ->set('get_parameter_name', $values['get_parameter_name'])
         ->set('extensions_blacklist', $values['extensions_blacklist'])
         ->set('extensions_whitelist', $values['extensions_whitelist'])
         ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @todo validate blacklist extensions against whitelist
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $get_paramater_name = $form_state->getValue('get_parameter_name');
    $invalid_params = $this->fileVersion->getInvalidQueryParameterNames();

    if (in_array($get_paramater_name, $invalid_params)) {
      $form_state->setError(
        $form['get_parameter_name'],
        $this->t('Parameter name can\'t be one of @invalid_params.',
          [
            '@invalid_params' => implode(', ', $invalid_params),
          ])
      );
    }
  }

}
