<?php


//////////////////////////////////////////////////////////////////////////////////////
//
//  NAME: cURL Handler
//  
//  DESCRIPTION: This script takes the basic cURL handling found in PHP and builds
//  failure handling into it by default.  Requests sent with this will automatically retry
//  upon failures, which will also occurr as a result of timeouts.  Standardized error 
//  handling is included as well.
//  
//  USAGE: This is useful in any application where requests need to be made via cURL
//  where a certain level of error correction and fallback handling is needed.
//  
//  DEPENDENCIES: Constants.php
//  
//  AUTHOR: Doug Gatza
//  
//  NOTES: N/A
// 
//////////////////////////////////////////////////////////////////////////////////////

class cURLHandler {

	const API_LOAD_TIMEOUT			= 4;
	const API_CURL_TIMEOUT			= 10;
	const API_RETRY_ATTEMPTS		= 1; // The number of times an API request should retry if eligible.
	const API_RETRY_DELAY			= 2; // The amount of time in seconds, to delay between connect attempts.

	// Beachbody defined exceptions
	const ERROR_API_LOAD 			= 200; // Error occurred while contacting remote API.
	const ERROR_API_LOAD_DATA 		= 201; // The remote API failed to load.
	const ERROR_API_LOAD_TIMEOUT  	= 202; // Encountered a timout while attempting to contact API.
	const ERROR_API_POST  			= 210; // Error occurred while posting to remote API.
	const ERROR_API_POST_DATA 	 	= 211; // The remove API failed to post.
	const ERROR_API_POST_TIMEOUT  	= 212; // Encountered a timout while attempting to post to API.


	// Makes a call to a remote page or API to retrieve data.
	/***************************************************************************/
	public static function requestData($url, array $postData = null, array $headers = null, $debug = false) {
		$postingData = ($postData !== null);
		echo '<div>
		 <h3><a href="#" id="debugRequest1">First</a></h3>
		</div>
		<div>';

		// Put a for loop around the API request in the event that multiple attempts need to be made.
		for ($i = 0; $i < (self::API_RETRY_ATTEMPTS + 1); $i++) {
		    
		    try {
		    	echo '<div>
				 <h3><a href="#" id="debugTryLoop1">First</a></h3>
				</div>
				<div>';

				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $url);
				/*
				if ($postingData) { curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); }
				
				if(isset($headers)){
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				}
				
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,self::API_LOAD_TIMEOUT); 
				curl_setopt($ch, CURLOPT_TIMEOUT, self::API_CURL_TIMEOUT);
				*/
				
				$result = curl_exec($ch);

				echo '<div>
				 <h3><a href="#" id="debugTryLoop2">First</a></h3>
				</div>
				<div>';
				

				// Note: Prints the cURL request URL and response, but only when debug is passed as 'true'.
				/***************************************************************************/
				
				if ($debug) {
					if ($postingData) { $querystring = implode("&", array_map( function ($v, $k) { return sprintf("%s=%s", $k, $v); }, $postData, array_keys($postData) )); }

					echo "[BB] GET DATA: URL = ".$url.(($postingData) ? "?".$querystring."<br />" : "<br />");
					echo "[BB] GET DATA: Result = ".$result."<br/><br/>";
				}

				/***************************************************************************/
				// Note: Prints the cURL request URL and response, but only when debug is passed as 'true'.
				

				// If the CURL operation returns an error, return it back to the caller.
				if ($result === false) {

					echo '<div>
					 <h3><a href="#" id="debugTryLoop3">First</a></h3>
					</div>
					<div>';
					echo "errorCURL";
					
					// If the error indicates a timeout, return our BB timeout error code.
					if (curl_errno($ch) === self::ERROR_API_CURL_TIMEOUT) {
						echo "errorCURL1";
						echo '<div>
						 <h3><a href="#" id="debugTryLoop3Error1">First</a></h3>
						</div>
						<div>';
						$result = $this->returnQualifiedErrorResponse((($postingData) ? self::ERROR_API_POST_TIMEOUT : self::ERROR_API_LOAD_TIMEOUT), "While attempting to ".(($postingData) ? "post data to" : "retrieve data from")." our video server, the connection timed out.  Please try refreshing this page.");

					// If the error is a general PHP exception, return that error now.
					} else {
						echo "errorCURL2";
						echo '<div>
						 <h3><a href="#" id="debugTryLoop3Error2">First</a></h3>
						</div>
						<div>';
						$result = $this->returnQualifiedErrorResponse((($postingData) ? self::ERROR_API_POST_DATA : self::ERROR_API_LOAD_DATA), "Malformed data was returned after attempting to ".(($postingData) ? "post data to" : "retrieve data from")." our video server.  Please try refreshing this page.<br/><br/>[ReferenceID: ".curl_errno($ch)."]");
					}
				}

				echo '<div>
				 <h3><a href="#" id="debugTryLoop4">First</a></h3>
				</div>
				<div>'; 

				curl_close($ch);

			// If an uncaught error occurs while trying to load the API, pass a general BB error back to the caller.
			} catch(Exception $e) {
				echo '<div>
				 <h3><a href="#" id="debugExceptionLoop">First</a></h3>
				</div>
				<div>';

				$result = $this->returnQualifiedErrorResponse((($postingData) ? self::ERROR_API_POST : self::ERROR_API_LOAD), "An error occurred while attempting to ".(($postingData) ? "post data to" : "retrieve data from")." our video server.  Please try refreshing this page.<br/><br/>[ReferenceID: ".$e->getCode()." | ".$e->getLine()." | ".$e->getMessage()."]");
			}


			// If there's an error code, check whether it should retry now.
		    if (isset($result->errorCode)) {
		    	echo '<div>
				 <h3><a href="#" id="debugErrorLoop">First</a></h3>
				</div>
				<div>';
		    	
		    	// If the error code is recoverable, try again
		    	if ($result->errorCode === self::ERROR_API_CURL_TIMEOUT) {		    		
		    		
		    		// If more connect attempts are allowed, proceed.
		    		if (($i + 1) < self::API_RETRY_ATTEMPTS) {
		    			sleep(self::API_RETRY_DELAY);
		    		}
		    	// IF the error code is fatal, break the loop now and progress forward.
		    	} else {
		    		break;
		    	}

		    // If there are no error codes, break the loop and progress forward.
		    } else {
		    	break;
		    }
		}
		echo '<div>
		 <h3><a href="#" id="debugRequest2">First</a></h3>
		</div>
		<div>';

		return $result;
	}


	// Returns an error response object using the passed error code and message.
	/***************************************************************************/
	public static function returnQualifiedErrorResponse($errorCode, $errorMessage) {
		$response = new stdClass();
		$response->errorCode = $errorCode;
		$response->userMessage = str_replace("'", "\'", $errorMessage);
		
		return $response;
	}

}

?>

<?php

//echo cURLHandler::requestData("https://profile-qa4-origin.api.beachbodyondemand.com/video/authorize?videoId=22HC0002B02&profileId=92ACBC10-4795-42E1-95EF-D66EA9649C50&linkId=3MNvhIFHYaMi&platform=web&language=english", null, null, true);
	$userguid = "ABCD";
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Mocha Tests</title>
	
	  <script
  src="https://code.jquery.com/jquery-1.12.4.min.js"
  integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
  crossorigin="anonymous"></script>
    
  </head>
  <body>
    
    <input type="button" value="Star" id="click-here" onclick="change()"></input>
    <script>
	    console.log("********** PRE-TEST ***********");
	    
	    var a 			= "<?php echo $userguid ?>";
	    console.log("passed point a");

	    var b 			= "<?php echo $feedData ?>";
	    console.log("passed point b");

	    var c			= "<?php echo isset($feedData) ?>" ? <?php echo json_encode($feedData) ?> : null;
	    console.log("passed point c");
     
    </script>
  </body>
</html>


