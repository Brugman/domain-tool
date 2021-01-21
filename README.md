# Domain Tool

> Analyze a domain name in seconds. Save yourself time, or impress a client on the phone.
>
> “Looks like your website is hosted with X and your domain is over at Y. Do those names sound familiar?”

![screenshot](/screenshot.png)

## Requirements

- Apache
- PHP
- Composer
- npm

## Installation

1. `composer i`
1. `npm i`
1. Copy `.env.example` to `.env` and configure it.
1. Map `public_html` to a (sub)domain.

## Required configuration

Configuration is done in the `.env` file.

`APP_ENV`: Set this to `local` and no password check will be performed.<br>
`APP_DEBUG`: Do you want to see errors?<br>
`APP_PASSWORD`: If you set a password then load the app with `?password=YOURPASSWORD`.
`APP_API_KEY_WXA`: API key for WHOIS service by [WhoisXMLAPI.com](https://whois.whoisxmlapi.com/).

## Contributing

Found a bug? Anything you would like to ask, add or change? Please open an issue so we can talk about it.

Pull requests are welcome. Please try to match the current code formatting.

## Disclaimer

This app uses your server to ping remote websites. If you open this app up for public use, others will be able send those pings on behalf of your server. Too many pings can be considered a Denial of Service attack, and you as the server owner may be penalized. We recommended running this app either locally, or online at a secret URL with a strong password.

## Author

[Tim Brugman](https://github.com/Brugman)
