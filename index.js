const chromeLauncher = require('chrome-launcher');
const CDP = require('chrome-remote-interface');
test = require('tape');

(async function() {
  async function launchChrome() {
    return await chromeLauncher.launch({
      chromeFlags: [
        '--disable-gpu',
        '--headless'
      ]
    });
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
  


  
  protocol.close();
  chrome.kill(); 
  });
  


})();
