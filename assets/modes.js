import './styles/modes.scss';

import $ from 'jquery';
import './components/bootstrap-toggle';

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

$(function () {
    $('#toggle1')[0].parentElement.addEventListener('click', function () {
        $('#toggle1spinner')[0].removeAttribute("hidden");
        $.ajax({
            url: '/settings/modes/toggle',
            type: 'POST',
            timeout: 30000, // 30s
            dataType: 'json',
            data: {
                "id": "1"
            },
            async: true,
            error: function () {
                showStatus('1', "Erreur de changement d'état !", "error");
                $("#toggle1").bootstrapToggle('toggle');
            },
            success: function (data) {
                if (data["id"] === "1") {
                    if (data["state"] === "Error") {
                        showStatus('1', "Erreur de changement d'état !", "error");
                        $("#toggle1").bootstrapToggle('toggle');
                    } else if (data["state"] === 1) {
                        showStatus('1', "Activé.", "success");
                        // document.documentElement.style.setProperty("--default-theme", "#177952");
                        // document.documentElement.style.setProperty("--stockmarket-theme", "#177952");
                        // document.getElementById("logo").setAttribute("src", "/images/logo_sm_mode.png");
                        $("#toggle1").bootstrapToggle('on');
                    } else if (data["state"] === 0) {
                        showStatus('1', "Désactivé.", "success");
                        // document.documentElement.style.setProperty("--default-theme", "#01A867");
                        // document.documentElement.style.setProperty("--stockmarket-theme", "#01A867");
                        // document.getElementById("logo").setAttribute("src", "/images/logo.png");
                        $("#toggle1").bootstrapToggle('off');
                    } else {
                        console.log("Erreur de changement d'état !");
                    }
                    sleep(2000).then(() => {
                        window.location.reload();
                    });
                } else {
                    console.log("Erreur de changement d'état !");
                }
                // console.log(data);
            }
        }).always(function () {
            $('#toggle1spinner')[0].setAttribute("hidden", "");
        });
        return false;
    });
});

function showStatus(id, message, status) {
    document.getElementById("toggle" + id + "status").setAttribute("shown", "true");
    if (status === "error") {
        document.getElementById("toggle" + id + "status").setAttribute("success", "false");
    } else if (status === "success") {
        document.getElementById("toggle" + id + "status").setAttribute("success", "true");
    } else {
        document.getElementById("toggle" + id + "status").setAttribute("success", "undefined");
    }
    document.getElementById("toggle" + id + "status").innerText = message;
    setTimeout(function () {
        document.getElementById("toggle" + id + "status").setAttribute("shown", "false");
    }, 3000); // in milliseconds
}
