# Discord Oauth2 PHP Framework
*The library is a simple gate trough Discord's Oauth2 Web API, Based on PHP cURL  
It is more easier, less complicated and more practical for multiple uses*

### **Requirements**

 - PHP *( Built for php7, but works fine for php5.4 and above )*
 - cURL extension installed
 - Valid Discordapp

### **Usage**

- Save [DiscordOauth2.php](https://github.com/elkhayder/Discord-Oauth2/blob/master/DiscordOauth2.php "DiscordOauth2.php on Github") in your working folder and include it on your desired file :
```php
require_once("path/to/DiscordOauth2.php");
```
- Create new Class :
```php
$request = new DiscordOauth2(
    "APP ID",
    "APP SECRET",
    "Redirect URL",
    "Scopes (array)"
);
```
- I programmed the Framework to throw Exceptions, so it is better to use it inside Try / Catch statements ...
```php
try {
    // Program Code
} catch (Exception $e) {
    echo $e->getMessage();
}
```
- The Framework is pretty easy to Read, U can take a look on it to understand it more. I suggest referring to [Discord Oauth2 API](https://discordapp.com/developers/docs/topics/oauth2) page too
### **Use Example**
```php
require_once("path/to/DiscordOauth2.php");
try {
    $request = new DiscordOauth2( // ALL data are provided by Discordapp.com
        "6906082258965796170", 
        "teqTdVfHST1f7OxzIwEr-mPou0bMTn7U",
        "https://www.mywebsite.com/DiscordOauthHandle.php",
        [
            "identify",
            "email",
            "connections",
            "guilds"
        ]
    );
} catch (Exception $e) {
    die("Error : " . $e->getMessage()); // Die to errors
}

if(!isset($_GET['code'])) { // Check for Code using GET Method.
    try  {
        $authorizationLink = $request->getAuthorizationUrl(); // Generate Authorization link
        echo '<a href="' . $authorizationLink . '">Connect with Discord</a>'; // Prints Auth link as a HTML link
    } catch (Exception $e) {
        die("Error : " . $e->getMessage()); // Die to errors
    }
} else {
    try {
        $code = $_GET['code'];
        $tokenRequest = $request->exchangeCode('authorization_code', $code); // Returns array
        $authToken = $tokenRequest['access_token']; // Get access token from tokens request
        $client = $request->fetchData($authToken); // Fetch User data
        // ↓ Use Example ↓
        $clientGuilds = $client['guilds'];
    } catch (Exception $e) {
        die("Error : " . $e->getMessage()); // Die to errors
    }
}
```

### **License**
The framework is licensed Under MIT license, Please fell free to use it at anyway you would like,   
Please reffer to [LICENSE](https://github.com/elkhayder/Discord-Oauth2/blob/master/LICENSE "LICENSE") for more.

### **Social Networking**
- **Github** : [elkhayder](https://www.github.com/elkhayder)
- **Facebook** : [Zakaria Elkhayder](https://www.facebook.com/ElkhayDerZakaria.II)
- **Instagram** : [elkhayder.zakaria](https://www.instagram.com/elkhayder.zakaria/)
- **Mail** : [zelkhayder@gmail.com](mailto:zelkhayder@gmail.com)
