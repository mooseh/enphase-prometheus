<!DOCTYPE html>
<!--
  Conditional classes for cross browser compatibility:
    http://paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/
 -->
<!--[if lt IE 7 ]> <html class="ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" lang="en"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9" lang="en"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Envoy 122243082722</title>
    <link href="/backbone/application.css?version=07.00.20" media="all" rel="stylesheet" type="text/css" />
    <script>
        window.BackboneConfig = {
            serial: "122243082722",
            profiles: false,
            show_prompt: false,
            internal_meter: true,
            software_version: "D7.0.88 (5580b1)",
            envoy_type: "EU",
            polling_interval: 300000,
            polling_frequency: 60,
            backbone_public: true,
            cte_mode: false,
            toolkit: false,
            max_errors: 0,
            max_timeouts: 0,
            e_units: "sig_fig",
            authentication_authority_url: "https://entrez.enphaseenergy.com/"
        }
    </script>
    <script src="/backbone/application.js?version=07.00.20" type="text/javascript"></script>
</head>
<body>
<script type="text/javascript">
    $(function() {
        I18n.defaultLocale = "en";
        I18n.locale = "en";
        I18n.fallbacks = true;
    });
</script>
<div id="now_{{ now()->timestamp }}"></div>
</body>
</html>
