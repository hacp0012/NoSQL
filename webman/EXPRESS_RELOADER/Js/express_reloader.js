/**
 * AUTO RELOADER
 * @author Congo Cloud Computer (DarDev - 0012)
 * @copyright 2023 3c-numeric.com
 * @link 3c-numeric.com
 * Auto Reloader is for use in DEV mode
 * if you go in Production you will set Auto to False
 * @param {Boolean} Auto make programme auto reaload on each action on file.
 * @param {Number} PORT a port to use  enstead default of Observer
 * @param {Boolean} Persistent if set to false the fun will down when connection is loseted with observer
 * this require to the Dev to reload page and try another connection to observer
 * @returns return null value
 */
function ExpressReloader(Auto = true, PORT = null, Persistent = true) {
  if (Auto == false) return null; // ! Exit fn
  if (PORT == null) PORT = 1337; // * Set default

  console.log("Express Observer - START");

  // Send requests sequentially so as to not overload the server
  let PID = 0;
  let update = function () {
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      if (xhttp.readyState == 4 && xhttp.status == 200) {
        const response = xhttp.responseText;
        // if no action is do
        if (response != "NO") {
          const respSplited = response.split(";");
          if (respSplited[1] == "3") {
            // THEN RELOAD THE PAGE
            location.reload();
          }
        }
      }
    };
    xhttp.onerror = function () {
      console.warn("Express Reloader : failed to connect to the OBSERVER.");
      if (!Persistent) clearInterval(PID); // stop auto connect
      return null;
    };
    xhttp.onabort = function () {
      console.info("Express Reloader : Connection aborted");
    };
    xhttp.open("GET", `http://localhost:${PORT}/ican`, true);
    try {
      xhttp.send(null);
    } catch (error) {
      console.error("Cant connect");
    }
  };
  PID = setInterval(update, 1000);
  return null;
}

// * START AUTOMATICALY THE OBSERVER *

ExpressReloader(true, null, false); // ? Default : automaticaly start waching fo reaload
// ExpressReloader(false)  // ? Disable auto reloader. for use when you pass to PROD Mode
