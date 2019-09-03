function createIssue(ticketId, dropdown, selectedProject, ticketName, ticketContent, $message) {
    let dropdownProject = document.getElementById('dropdown_project' + dropdown);
    let newSelectedProject = dropdownProject.options[dropdownProject.selectedIndex].value;

    creatAgain = true;

    if (selectedProject == newSelectedProject) {
        creatAgain = confirm($message);
    }

    if (creatAgain) {
        jQuery.ajax({
            type: "POST",
            url: "../plugins/gitlabintegration/ajax/issue.php",
            data: {
                selectedProject: newSelectedProject,
                ticketId: ticketId,
                ticketName: ticketName,
                ticketContent: ticketContent
            }
        })
            .success(function () {
                location.reload();
            })
            .fail(function () {
                return false;
            });
    }
}

function setSelectedProject(dropdown, selectedProject) {
    let dropdownProject = document.getElementById('dropdown_project' + dropdown);

    if (selectedProject) {
        for (var i, j = 0; i = dropdownProject.options[j]; j++) {
            if (i.value == selectedProject) {
                dropdownProject.selectedIndex = j;
                break;
            }
        }
        let span = document.getElementById('select2-dropdown_project' + dropdown + '-container');
        span.textContent = dropdownProject.options[dropdownProject.selectedIndex].text;
        span.title = dropdownProject.options[dropdownProject.selectedIndex].text;
    }
}

function addProfile(dropdown, userId) {
    let dropdownProfile = document.getElementById('dropdown__profiles_id' + dropdown);
    let newProfileSelected = dropdownProfile.options[dropdownProfile.selectedIndex].value;

    jQuery.ajax({
        type: "POST",
        url: "../ajax/profile.php",
        data: {
            profileId: newProfileSelected,
            userId: userId,
            modo: 1,
            id: 0
        }
    })
        .success(function () {
            window.open("../front/profiles.php", "_self");
        })
        .fail(function () {
            return false;
        });
}

function setClickCheckAll(checkboxName, principal) {
    checkboxName = 'checkAll_' + checkboxName;
    let checkbox = document.getElementsByName(checkboxName);
    checkbox[0].addEventListener("click", function () {
        if (principal) {
            checkAllBoxes(checkboxName, checkbox[0].checked);
        } else {
            changeCheckAll();
        }
    }, false);
}

function checkAllBoxes(checkboxName, selected) {
    let inputs = document.getElementsByTagName("input");
    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].type == "checkbox") {
            if (inputs[i].name != checkboxName) {
                inputs[i].checked = selected;
            }
        }
    }
}

function changeCheckAll() {
    let inputsData = document.getElementById('data').getElementsByTagName('input');

    let i;
    let countSelected = 0;
    for (i = 0; i < inputsData.length; i++) {
        if (inputsData[i].type == "checkbox") {
            if (inputsData[i].checked) {
                countSelected++;
            }
        }
    }

    let inputTop = document.getElementById('principal_1').getElementsByTagName('input');
    let inputBotton = document.getElementById('principal_2').getElementsByTagName('input');

    if (countSelected == inputsData.length) {
        changeCheckInput(inputTop, true);
        changeCheckInput(inputBotton, true);
    } else {
        changeCheckInput(inputTop, false);
        changeCheckInput(inputBotton, false);
    }
}

function changeCheckInput(inputArray, checked) {
    for (i = 0; i < inputArray.length; i++) {
        if (inputArray[i].type == "checkbox") {
            inputArray[i].checked = checked;
        }
    }
}

function openActions() {
    countCheckSelected();
    let div = document.getElementById('favDialog');
    $(div).dialog();
}

function countCheckSelected() {
    let inputsData = document.getElementById('data').getElementsByTagName('input');

    let countSelected = 0;
    for (i = 0; i < inputsData.length; i++) {
        if (inputsData[i].type == "checkbox") {
            if (inputsData[i].checked) {
                countSelected++;
            }
        }
    }
    if (countSelected == 0) {
        document.getElementById("no_information").style.visibility = "visible";
        document.getElementById("options_to_select").style.visibility = "hidden";
        document.getElementById("button_confirm_action").style.visibility = "hidden";
    } else {
        document.getElementById("no_information").style.visibility = "hidden";
        document.getElementById("options_to_select").style.visibility = "visible";
        document.getElementById("button_confirm_action").style.visibility = "visible";
    }
}

function removePermission(dropdown) {
    let dropdownActions = document.getElementById('dropdown_actions' + dropdown);
    let selectedAction = dropdownActions.options[dropdownActions.selectedIndex].value;

    if (selectedAction == 0) {
        let div = document.getElementById('favDialog');
        $(div).dialog('close');
    } else {
        let inputsData = document.getElementById('data').getElementsByTagName('input');

        let countSelected = 0;
        let idProfilesSelected = [];
        for (i = 0; i < inputsData.length; i++) {
            if (inputsData[i].type == "checkbox") {
                if (inputsData[i].checked) {
                    profile = inputsData[i].name.split("_");
                    idProfilesSelected[countSelected] = profile[2];
                    countSelected++;
                }
            }
        }

        idProfilesSelected.forEach(element => {
            jQuery.ajax({
                type: "POST",
                url: "../ajax/profile.php",
                data: {
                    profileId: 0,
                    userId: 0,
                    modo: 0,
                    id: element
                }
            })
                .success(function () {
                    window.open("../front/profiles.php", "_self");
                })
                .fail(function () {
                    return false;
                });
        });
    }
}
