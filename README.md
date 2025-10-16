Baransys Referer Recognition WordPress Plugin

Overview

This plugin captures referrer and campaign parameters from incoming requests (URL parameters or cookies) and makes them available to Gravity Forms hidden fields. It also sets compatible cookies so front-end scripts and forms can populate hidden fields with referral data.

Features

- Detects utm_source, utm_medium, utm_campaign and alternate parameter names (source1, medium1).
- Stores referral and campaign data in cookies (brr_referer, brr_medium, brr_campaign and other ad/campaign params).
- Populates existing Gravity Forms hidden fields when their `inputName` matches configured mapping.
- AJAX endpoint to retrieve Gravity Forms field mapping for front-end scripts.
- Admin notice if Gravity Forms is not active.

Installation

1. Copy the plugin folder to your WordPress `wp-content/plugins/` folder.
2. Activate the plugin through the WordPress admin dashboard (Plugins > Installed Plugins).
3. Ensure the Gravity Forms plugin is installed and active if you want automatic population of Gravity Forms fields.

Configuration

- Settings: Go to Settings → "تنظیمات آی دی گراویتی فرم " and set the Gravity Form ID(s) (comma-separated) you want the plugin to target.

Gravity Forms setup

- In your Gravity Form, add hidden fields where you want the referer/campaign values to go.
- For each hidden field set the Input Name (in Field Settings → Advanced → Input Name) to one of the following values to map them:

  - brrReferer
  - brrSource
  - brrMedium
  - brrCampaign
  - brrAdGroup
  - brrMatchType
  - brrKeyword
  - brrCampaignContent
  - brrCampaignTerm
  - brrCampaignId

How it works

- On page load, the plugin's front-end script (`assets/baran_referer.js`) inspects URL parameters and document.referrer, sets cookies, and triggers an AJAX call to fetch the form-field mapping so it can fill visible inputs where appropriate.
- On server-render, the plugin checks the configured Gravity Form IDs and fills `field->defaultValue` for any matching hidden fields using cookie or URL param values.

Cookie names used

- brr_referer
- brr_medium
- brr_campaign
- http_g_set_already
- utm_campaign_set_already
- ad_group (adgroup_id in some URLs)
- matchtype
- keyword
- campaign_content
- campaign_term
- campaign_id

Testing

1. Visit a page on your site with UTM parameters, e.g. `https://example.com/?utm_source=google&utm_medium=cpc&utm_campaign=test`.
2. Open DevTools → Application → Cookies and verify the plugin set `brr_referer`, `brr_medium`, `brr_campaign` cookies.
3. Open the page containing the targeted Gravity Form and inspect the form HTML or submit a test entry — the hidden inputs should contain the cookie/utm values.
4. If the Gravity Forms plugin is not active the plugin will show an admin notice instructing to activate Gravity Forms.

Troubleshooting

- If hidden fields are not populated:
  - Verify the Gravity Form ID(s) are set in plugin settings.
  - Verify the hidden fields' Input Name matches one of the mapping values above.
  - Check browser cookies to ensure the plugin saved the values.
  - Check the browser console for any JS errors from `assets/baran_referer.js`.

Developer notes

- The plugin uses WordPress APIs (actions/filters, wp_send_json_*, wp_create_nonce, etc.). Tests should run inside a WordPress environment.
- Cookie settings allow front-end JS to read cookie values (httponly false) so the script can prefill client-side form inputs.

License

GPLv2 or later

Contact

For questions or support, open an issue in the repository or contact the developer.