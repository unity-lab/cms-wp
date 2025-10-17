<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Integrations\MailPoet;

if (!defined('ABSPATH')) exit;


use Automattic\WooCommerce\EmailEditor\Email_Editor_Container;
use Automattic\WooCommerce\EmailEditor\Engine\Settings_Controller;
use Automattic\WooCommerce\EmailEditor\Engine\Theme_Controller;
use Automattic\WooCommerce\EmailEditor\Engine\User_Theme;
use MailPoet\Analytics\Analytics;
use MailPoet\Config\Env;
use MailPoet\Config\Installer;
use MailPoet\Config\ServicesChecker;
use MailPoet\EmailEditor\Integrations\MailPoet\EmailEditor as EditorInitController;
use MailPoet\Entities\NewsletterEntity;
use MailPoet\Newsletter\NewslettersRepository;
use MailPoet\Settings\SettingsController as MailPoetSettings;
use MailPoet\Settings\UserFlagsController;
use MailPoet\Util\CdnAssetUrl;
use MailPoet\Util\License\Features\Subscribers as SubscribersFeature;
use MailPoet\WP\Functions as WPFunctions;

class EditorPageRenderer {
  private WPFunctions $wp;

  private Settings_Controller $settingsController;

  private Theme_Controller $themeController;

  private User_Theme $userTheme;

  private DependencyNotice $dependencyNotice;

  private CdnAssetUrl $cdnAssetUrl;

  private ServicesChecker $servicesChecker;

  private SubscribersFeature $subscribersFeature;

  private MailPoetSettings $mailpoetSettings;

  private NewslettersRepository $newslettersRepository;

  private UserFlagsController $userFlagsController;

  private Analytics $analytics;

  public function __construct(
    WPFunctions $wp,
    CdnAssetUrl $cdnAssetUrl,
    ServicesChecker $servicesChecker,
    SubscribersFeature $subscribersFeature,
    DependencyNotice $dependencyNotice,
    MailPoetSettings $mailpoetSettings,
    NewslettersRepository $newslettersRepository,
    UserFlagsController $userFlagsController,
    Analytics $analytics
  ) {
    $this->wp = $wp;
    $this->settingsController = Email_Editor_Container::container()->get(Settings_Controller::class);
    $this->cdnAssetUrl = $cdnAssetUrl;
    $this->servicesChecker = $servicesChecker;
    $this->subscribersFeature = $subscribersFeature;
    $this->themeController = Email_Editor_Container::container()->get(Theme_Controller::class);
    $this->userTheme = Email_Editor_Container::container()->get(User_Theme::class);
    $this->dependencyNotice = $dependencyNotice;
    $this->mailpoetSettings = $mailpoetSettings;
    $this->newslettersRepository = $newslettersRepository;
    $this->userFlagsController = $userFlagsController;
    $this->analytics = $analytics;
  }

  public function render() {
    $postId = isset($_GET['post']) ? intval($_GET['post']) : 0;
    $post = $this->wp->getPost($postId);
    $currentPostType = $post->post_type; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

    if (!$post instanceof \WP_Post || $currentPostType !== EditorInitController::MAILPOET_EMAIL_POST_TYPE) {
      return;
    }
    $newsletter = $this->newslettersRepository->findOneBy(['wpPost' => $postId]);
    if (!$newsletter instanceof NewsletterEntity) {
      return;
    }
    $this->dependencyNotice->checkDependenciesAndEventuallyShowNotice();

    // load analytics (mixpanel) library
    if ($this->analytics->isEnabled()) {
      add_filter('admin_footer', [$this, 'loadAnalyticsModule'], 24);
    }

    // load mailpoet email editor JS integrations
    $editorIntegrationAssetsParams = require Env::$assetsPath . '/dist/js/email_editor_integration/email_editor_integration.asset.php';
    $this->wp->wpEnqueueScript(
      'email_editor_integration',
      Env::$assetsUrl . '/dist/js/email_editor_integration/email_editor_integration.js',
      $editorIntegrationAssetsParams['dependencies'],
      $editorIntegrationAssetsParams['version'],
      true
    );
    $this->wp->wpEnqueueStyle(
      'email_editor_integration',
      Env::$assetsUrl . '/dist/js/email_editor_integration/email_editor_integration.css',
      [],
      $editorIntegrationAssetsParams['version']
    );

    // Email editor rich text JS - Because we Personalization Tags depend on Gutenberg 19.8.0 and higher
    // the following code replaces used Rich Text for the version containing the necessary changes.
    $assetsParams = require Env::$assetsPath . '/dist/js/email-editor/assets/rich-text.asset.php';
    $this->wp->wpDeregisterScript('wp-rich-text');
    $this->wp->wpEnqueueScript(
      'wp-rich-text',
      Env::$assetsUrl . '/dist/js/email-editor/assets/rich-text.js',
      $assetsParams['dependencies'],
      $assetsParams['version'],
      true
    );
    // End of replacing Rich Text package.
    $styleParams = require Env::$assetsPath . '/dist/js/email-editor/style/style.asset.php';
    $this->wp->wpEnqueueStyle(
      'mailpoet_email_editor',
      Env::$assetsUrl . '/dist/js/email-editor/style/style.css',
      [],
      $styleParams['version']
    );

    // The email editor needs to load block categories to avoid warning and missing category names.
    // See: https://github.com/WordPress/WordPress/blob/753817d462955eb4e40a89034b7b7c375a1e43f3/wp-admin/edit-form-blocks.php#L116-L120.
    wp_add_inline_script(
      'wp-blocks',
      sprintf('wp.blocks.setCategories( %s );', wp_json_encode(get_block_categories($post))),
      'after'
    );

    // Preload server-registered block schemas to avoid warning about missing block titles.
    // See: https://github.com/WordPress/WordPress/blob/753817d462955eb4e40a89034b7b7c375a1e43f3/wp-admin/edit-form-blocks.php#L144C1-L148C3.
    wp_add_inline_script(
      'wp-blocks',
      sprintf('wp.blocks.unstable__bootstrapServerSideBlockDefinitions( %s );', wp_json_encode(get_block_editor_server_block_settings()))
    );

    $editorSettings = $this->settingsController->get_settings();
    $editorSettings['displaySendEmailButton'] = true;

    $currentUserEmail = $this->wp->wpGetCurrentUser()->user_email;

    $isAutomationNewsletter = $newsletter->isAutomation() || $newsletter->isAutomationTransactional();
    $automationId = $newsletter->getOptionValue('automationId');

    $listingUrl = 'page=mailpoet-newsletters';
    $sendUrl = 'page=mailpoet-newsletters#/send/' . $newsletter->getId();
    $backUrl = 'page=mailpoet-newsletters';

    if ($isAutomationNewsletter) {
      $listingUrl = 'page=mailpoet-automation';
      $sendUrl = 'page=mailpoet-automation-editor&id=' . $automationId;
      $backUrl = $sendUrl;
    }

    $this->wp->wpLocalizeScript(
      'email_editor_integration',
      'WooCommerceEmailEditor',
      [
        'current_post_type' => esc_js($currentPostType),
        'current_post_id' => $post->ID,
        'current_wp_user_email' => esc_js($currentUserEmail),
        'editor_settings' => $editorSettings,
        'editor_theme' => $this->themeController->get_base_theme()->get_raw_data(),
        'user_theme_post_id' => $this->userTheme->get_user_theme_post()->ID,
        'urls' => [
          'listings' => admin_url('admin.php?' . $listingUrl),
          'send' => admin_url('admin.php?' . $sendUrl),
          'back' => admin_url('admin.php?' . $backUrl),
        ],
      ]
    );

    $installedAtDiff = (new \DateTime($this->mailpoetSettings->get('installed_at')))->diff(new \DateTime());
    // Survey should be displayed only if there are 2 and more emails and the user hasn't seen it yet
    $displaySurvey = ($this->newslettersRepository->getCountOfEmailsWithWPPost() > 1) && !$this->userFlagsController->get(UserFlagsController::EMAIL_EDITOR_SURVEY);

    // Renders additional script data that some components require e.g. PremiumModal. This is done here instead of using
    // PageRenderer since that introduces other dependencies we want to avoid. Used by getUpgradeInfo.
    // some of these values are used by the powered by mailpoet block: mailpoet/assets/js/src/mailpoet-custom-email-editor-blocks/powered-by-mailpoet/
    $installer = new Installer(Installer::PREMIUM_PLUGIN_SLUG);
    $inline_script_data = [
      'mailpoet_premium_plugin_installed' => Installer::isPluginInstalled(Installer::PREMIUM_PLUGIN_SLUG),
      'mailpoet_premium_active' => $this->servicesChecker->isPremiumPluginActive(),
      'mailpoet_premium_plugin_download_url' => $this->subscribersFeature->hasValidPremiumKey() ? $installer->generatePluginDownloadUrl() : null,
      'mailpoet_premium_plugin_activation_url' => $installer->generatePluginActivationUrl(Installer::PREMIUM_PLUGIN_PATH),
      'mailpoet_has_valid_api_key' => $this->subscribersFeature->hasValidApiKey(),
      'mailpoet_has_valid_premium_key' => $this->subscribersFeature->hasValidPremiumKey(),
      'mailpoet_has_premium_support' => $this->subscribersFeature->hasPremiumSupport(),
      'mailpoet_plugin_partial_key' => $this->servicesChecker->generatePartialApiKey(),
      'mailpoet_subscribers_count' => $this->subscribersFeature->getSubscribersCount(),
      'mailpoet_subscribers_limit' => $this->subscribersFeature->getSubscribersLimit(),
      'mailpoet_subscribers_limit_reached' => $this->subscribersFeature->check(),
      // settings needed for Satismeter tracking
      'mailpoet_3rd_party_libs_enabled' => $this->mailpoetSettings->get('3rd_party_libs.enabled') === '1',
      'mailpoet_display_nps_email_editor' => $displaySurvey,
      'mailpoet_display_nps_poll' => true,
      'mailpoet_current_wp_user' => $this->wp->wpGetCurrentUser()->to_array(),
      'mailpoet_current_wp_user_firstname' => $this->wp->wpGetCurrentUser()->user_firstname,
      'mailpoet_cdn_url' => $this->cdnAssetUrl->generateCdnUrl(""),
      'mailpoet_site_url' => $this->wp->siteUrl(),
      'mailpoet_review_request_illustration_url' => $this->cdnAssetUrl->generateCdnUrl('review-request/review-request-illustration.20190815-1427.svg'),
      'mailpoet_installed_days_ago' => (int)$installedAtDiff->format('%a'),
      'mailpoet_is_automation_newsletter' => $isAutomationNewsletter,
      'mailpoet_automation_id' => $automationId,
    ];
    $this->wp->wpAddInlineScript('email_editor_integration', implode('', array_map(function ($key) use ($inline_script_data) {
      return sprintf("var %s=%s;", $key, wp_json_encode($inline_script_data[$key]));
    }, array_keys($inline_script_data))), 'before');

    // Load CSS from Post Editor
    $this->wp->wpEnqueueStyle('wp-edit-post');
    // Load CSS for the format library - used for example in popover
    $this->wp->wpEnqueueStyle('wp-format-library');

    // Enqueue media library scripts
    $this->wp->wpEnqueueMedia();

    $this->preloadRestApiData($post);

    require_once ABSPATH . 'wp-admin/admin-header.php';
    echo '<div id="mailpoet-email-editor" class="block-editor block-editor__container hide-if-no-js"></div>';
  }

  private function preloadRestApiData(\WP_Post $post): void {
    $userThemePostId = $this->userTheme->get_user_theme_post()->ID;
    $templateSlug = get_post_meta($post->ID, '_wp_page_template', true);
    $routes = [
      '/wp/v2/mailpoet_email/' . intval($post->ID) . '?context=edit',
      '/wp/v2/types/mailpoet_email?context=edit',
      '/wp/v2/global-styles/' . intval($userThemePostId) . '?context=edit', // Global email styles
      '/wp/v2/block-patterns/patterns',
      '/wp/v2/templates?context=edit',
      '/wp/v2/block-patterns/categories',
      '/wp/v2/settings',
      '/wp/v2/types?context=view',
      '/wp/v2/taxonomies?context=view',
    ];

    if ($templateSlug) {
      $routes[] = '/wp/v2/templates/lookup?slug=' . $templateSlug;
    } else {
      $routes[] = '/wp/v2/mailpoet_email?context=edit&per_page=30&status=publish,sent';
    }

    // Preload the data for the specified routes
    $preloadData = array_reduce(
      $routes,
      'rest_preload_api_request',
      []
    );

    // Add inline script to set up preloading middleware
    wp_add_inline_script(
      'wp-blocks',
      sprintf(
        'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );',
        wp_json_encode($preloadData)
      )
    );
  }

  public function loadAnalyticsModule() {  // phpcs:ignore -- MissingReturnStatement not required
    $publicId = $this->analytics->getPublicId();
    $isPublicIdNew = $this->analytics->isPublicIdNew();
    // this is required here because of `analytics-event.js` and order of script load and use in `mailpoet-email-editor-integration/index.ts`
    $libs3rdPartyEnabled = $this->mailpoetSettings->get('3rd_party_libs.enabled') === '1';

    // we need to set this values because they are used in the analytics.html file
    ?>
      <script type="text/javascript"> <?php // phpcs:ignore ?>
        window.mailpoet_analytics_enabled = true;
        window.mailpoet_analytics_public_id = '<?php echo esc_js($publicId); ?>';
        window.mailpoet_analytics_new_public_id = <?php echo wp_json_encode($isPublicIdNew); ?>;
        window.mailpoet_3rd_party_libs_enabled = <?php echo wp_json_encode($libs3rdPartyEnabled); ?>;
        window.mailpoet_version = '<?php echo esc_js(MAILPOET_VERSION); ?>';
        window.mailpoet_premium_version = '<?php echo esc_js((defined('MAILPOET_PREMIUM_VERSION')) ? MAILPOET_PREMIUM_VERSION : ''); ?>';
      </script>
    <?php

    include_once Env::$viewsPath . '/analytics.html';
  }
}
