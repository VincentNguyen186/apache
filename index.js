const chromeLauncher = require('chrome-launcher');
const CDP = require('chrome-remote-interface');
test = require('tape');

function sleep(waitTime) {
	return new Promise(resolve => {
    	setTimeout(() => {
      		resolve('resolved');
    	}, waitTime);
  	});
};

(async function() {
  async function launchChrome() {
    return await chromeLauncher.launch({
      chromeFlags: [
        '--disable-gpu',
        '--headless'
      ]
    });
  }
	
	async function eval(runtime, expression) {
		var result = await runtime.evaluate({
			expression: expression
		});
		return result.result.value;
	}
	
  const chrome = await launchChrome();
  const protocol = await CDP({
    port: chrome.port
  });

  // ALL FOLLOWING CODE SNIPPETS HERE
  const {
  DOM,
  Page,
  Emulation,
  Runtime
  } = protocol;
  await Promise.all([Page.enable(), Runtime.enable(), DOM.enable()]);
	
  Runtime.consoleAPICalled((arguments) => {
	console.log(arguments.args[0].value);
  });

  Page.navigate({
  url: 'http://localhost/test.php'
  //url: 'http://player-qa4.beachbodyondemand.com/video-interface.php?guid=22HC0002B02&display=Core+1&userGUID=92ACBC10-4795-42E1-95EF-D66EA9649C50&debug=true'
  });

  
  Page.loadEventFired(async() => {
    
  const result = await Runtime.evaluate({
    expression: "document.getElementById(\'click-here\').value"
  });
  console.log(result.result.value);

  
  test('pdk initialization testing', function(t) {
    t.plan(1);

    t.equal(1, 1, "Pdk Player Initialization.");
  });
    
    
    await sleep(2000);
		

		
		console.log("debugRequest1:", await eval(Runtime, "$(\'#debugRequest1\').length"));
		console.log("debugRequest2:", await eval(Runtime, "$(\'#debugRequest2\').length"));

		console.log("debugTryLoop1:", await eval(Runtime, "$(\'#debugTryLoop1\').length"));
		console.log("debugTryLoop2:", await eval(Runtime, "$(\'#debugTryLoop2\').length"));
		console.log("debugTryLoop3:", await eval(Runtime, "$(\'#debugTryLoop3\').length"));
		console.log("debugTryLoop3Error1:", await eval(Runtime, "$(\'#debugTryLoop3Error1\').length"));
		console.log("debugTryLoop3Error2:", await eval(Runtime, "$(\'#debugTryLoop3Error2\').length"));
		console.log("debugTryLoop4:", await eval(Runtime, "$(\'#debugTryLoop4\').length"));

		console.log("debugExceptionLoop:", await eval(Runtime, "$(\'#debugExceptionLoop\').length"));
		console.log("debugErrorLoop:", await eval(Runtime, "$(\'#debugErrorLoop\').length"));

		
  


  
  protocol.close();
  chrome.kill(); 
  });
  


})();
