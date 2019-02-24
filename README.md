# API for Swiss Lotto & Euro Millions

## Whats this for
This is a small PHP script which will/can get the lotto numbers from the last draw of Swiss Lotto & Euro Millions. You can install it with composer on your server and call it e.g. with domain.tld/?lotto=swisslotto. This will return a simple JSON.

Note: The PHP script will get the numbers with an HTML parser. This means this API will only work as long as the site of swisslotto.ch doesn't change their page structure.

## How to use
1. Clone repository
2. Make sure the web directory is your web root
3. Make composer install
4. Open the URL with domain.tld/?lotto=euromillions or domain.tld/?lotto=swisslotto
5. Enjoy your JSON
