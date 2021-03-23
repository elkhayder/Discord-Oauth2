<?php
class DiscordOauth2 {

    /*
    * Declaring variables
    */
    private $app_id;
    private $app_secret;
    private $redirect_url;
    private $scopes = [];

    /*
    * Constructing Function
    */
    public function __construct($app_id, $app_secret, $redirect_url, $scopes = []) {
        //if(!isset($app_id, $app_secret, $redirect_url, $scopes)) throw new Exception("Empty Constructor", 0);
        if(!in_array('curl', get_loaded_extensions())) throw new Exception("cURL isn't Installed / Activated"); // Chech for cURL extension

        if(!is_numeric($app_id)) throw new Exception("Invalid APP ID"); // Check APP Id

        // APP Secret Validation

        if(!filter_var($redirect_url, FILTER_VALIDATE_URL)) throw new Exception("Invalid Redirect URL"); // URL Validation

        if(!is_array($scopes)) throw new Exception("Invalid Scopes Format"); // Validate Scopes Format

        /*
        * Scopes
        */
        $scopes_lowercase = []; // Declaring Temp Lowercase Scopes Variable
        $avaliable_scopes = [ // valid Scopes Array
            "email",
            "connections",
            "identify",
            "guilds",
            //"guilds.join"
        ];
        foreach($scopes as $scope) { // Validate Scopes and Store them as LowerCase
            $lowercaseScope = strtolower($scope);
            if(!in_array($lowercaseScope, $avaliable_scopes)) throw new Exception("Invalid or unsupported Scope : " . $lowercaseScope);
            array_push($scopes_lowercase, $lowercaseScope);
        }

        // Store Data
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->redirect_url = $redirect_url;
        $this->scopes = $scopes_lowercase;
    }

    public function getAuthorizationUrl() {
        $AuthorizationUrl = "https://discordapp.com/api/oauth2/authorize?client_id=" . $this->app_id . "&redirect_uri=" . urlencode($this->redirect_url) . "&response_type=code&scope=" . implode("%20", $this->scopes);
        return $AuthorizationUrl;
    }

    public function exchangeCode($exchange_to, $code) {

        if(!isset($code) || $code == "") throw new Exception("Provided Code invalid"); // Checking provided Code

        /*
        * API Request
        */
        $data = [ // Request Data
            'client_id' => $this->app_id,
            'client_secret'=> $this->app_secret,
            'grant_type'=> strtolower($exchange_to),
            'redirect_uri'=> $this->redirect_url,
            'scope'=> implode(" ", $this->scopes)
        ];
        switch(strtolower($exchange_to)) {
            case "refresh_token": // Exchange type = Refresh token
                //global $code;
                $data['refresh_token'] = $code;
                break;
            case "authorization_code": // Exchange type = Authorization code
                //global $code;
                $data['code'] = $code;
                break;
            default: // No valid  exchange type
                throw new Error("Invalid Exchange Method");
        }

        /*
        * cURL request
        */
        $request = curl_init("https://discordapp.com/api/oauth2/token"); // Initiate Connection to Discord API
        curl_setopt($request, CURLOPT_POST, 1); // Set Post Method
        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($data)); // Set POST Data
        curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded')); // Request Header
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true); // Enable Response Return
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL Verification ( For testing purposes )
        $response = curl_exec($request); // Execute Request
        if (curl_errno($request)) throw new Exception("cURL request Error : ". curl_error($request)); // Check For cURL errors
        curl_close($request); // Close Connection

        /*
        * Handling Response
        */
        $response = json_decode($response, true); // Save Request Response as array
        if(isset($response["error"])) throw new Exception("Data error : " . ($response["error_description"]) ? $response["error_description"] : "Unknown Error"); // Return Discordapp Error
        return $response;
    }

    public function fetchData($token) {
        if(!isset($token) || $token == "") throw new Exception("Invalid Token"); // Checking provided token 
        $result = []; // Empty Results array
        $api_s = [ // Declaring API
            "https://discordapp.com/api/users/@me/connections" => "connections",
            "https://discordapp.com/api/users/@me/guilds" => "guilds",
            "https://discordapp.com/api/users/@me" => "identify"
        ];
        $QueueRequests = []; // Queue apis
        foreach($api_s as $api => $for) {
            if(in_array($for, $this->scopes)) array_push($QueueRequests, $api); // Adding Requests api to Queue
        }
        
        // Execute Queue
        foreach($QueueRequests as $QueueLink) {
            $request = curl_init($QueueLink);
            curl_setopt($request, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token)); // Request Header
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true); // Enable Response Return
            $response = curl_exec($request); // Execute
            if (curl_errno($request)) throw new Exception("cURL request Error : ". curl_error($request)); // Check For cURL errors
            curl_close($request); // Close Connection

            /*
            * Handling Response
            */
            $response = json_decode($response, true); // Save Request Response as array
            if(isset($response["error"])) throw new Exception("Data error : " . ($response["error_description"]) ? $response["error_description"] : "Unknown Error"); // Return Discordapp Error
            $result[$api_s[$QueueLink]] =  $response;//array_push($result, $response);
        }
        // return Result
        return $result;
    }
}
