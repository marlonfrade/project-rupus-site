
// Accordion animation
function toggleDrop(elm) {
    elm.parentElement.classList.toggle('dropdownHidden');
}


// Hover to show profile achievements
const profileArrow = document.querySelector('.profileAchievements');
const hoverProfile = document.querySelector('.hoverProfile');

hoverProfile.addEventListener('mouseleave', function(){
    hoverProfile.classList.remove('showHoverProfile');
})

profileArrow.addEventListener('click', () => {

    hoverProfile.classList.toggle('showHoverProfile');

});



//Show form
function showForm() {
    let elm = document.getElementById('formQuestion').parentElement;
    const body = document.querySelector('body');
    body.style.overflowY = 'hidden';
    elm.style.display = 'flex';
}

//Send form
async function validateForm(elm) {
    let formData = new FormData(elm);

    let userData = localStorage.getItem("userData")
    userData = JSON.parse(userData)

    const req = await fetch(`${BASE_API}/module/validate_answer`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Authorization': 'Bearer ' + userData.JWT
        },
        body: formData
    });

    const json = await req.json();
    if (json.error == 0 && json.data.toString()) {
        for (var pair of formData.entries()) {
            let inputs = document.querySelectorAll(`[name="${pair[0]}"]`);

            for (const item of inputs) {
                let attr = item.getAttribute('answer');
                item.parentElement.classList.remove('right', 'wrong');

                for (const key in json.data) {
                    if (key == attr && json.data[key].result == 1) {
                        item.parentElement.classList.add("right")
                    }
                    if (key == attr && json.data[key].result == 0) {
                        item.parentElement.classList.add("wrong")
                    }
                }
                // item.disabled = true;
            }

        }
    }
    info();
    alert('Respostas atualizadas!')
}

//Fetch form
async function fetchForm(module_id) {
    let userData = localStorage.getItem("userData")
    userData = JSON.parse(userData)

    const req = await fetch(`${BASE_API}/module/${module_id}/question`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Authorization': 'Bearer ' + userData.JWT
        }
    });
    const json = await req.json();
    if (json.error == 0 && json.data.length) {

        const questions = json.data;
        let moduleTemplate = `
        <div class="headerForm">
            <h4>Questionário</h4>
            <img src="./assets/img/close.svg" alt="X" class="closeModal" onclick="closeModal(this)">
        </div>`;
        for (const q of questions) {
            let question_id = q.id;
            let question = q.question;
            let answered = q.answered;
            let status = q.status;
            moduleTemplate += `
            <div class="question">
            <h5>${question}</h5>`;
            for (const answ of q.answer) {
                let answer_id = answ.id;
                let answer = answ.answer;

                if (answered && answered == answer_id) {
                    moduleTemplate += `
                    <label class="${parseInt(status) ? 'right' : 'wrong'}">
                        <input type="radio" checked name="answer${question_id}[]" answer="answer${answer_id}" value='{"modulo": ${module_id}, "question": ${question_id}, "answer": ${answer_id}}' required> ${answer}
                    </label>`;
                } else {
                    moduleTemplate += `
                    <label>
                        <input type="radio" name="answer${question_id}[]" answer="answer${answer_id}" value='{"modulo": ${module_id}, "question": ${question_id}, "answer": ${answer_id}}' required> ${answer}
                    </label>`;
                }

            }

            moduleTemplate += `</div>`;
        }
        moduleTemplate += `
        <div class="buttonForm">
            <button type="submit">Enviar respostas</button>
        </div>`;
        document.getElementById('formQuestion').innerHTML = moduleTemplate;
        showForm();
    }else{
        alert('Sem perguntas cadastradas!')
    }
}


function closeModal(elm) {
    const body = document.querySelector('body');
    const formDiv = elm.parentElement.parentElement.parentElement;

    body.style.overflowY = 'scroll';
    formDiv.style.display = 'none';
}


async function info() {
    const req = await fetch(`${BASE_API}/user/info`, {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            'Authorization': 'Bearer ' + userData.JWT
        }
    });

    const json = await req.json();
    let data = '';
    let moduleDone = 0;
    json.data.modules.forEach(item => {
        if (item.qtd_questions_answered_correct == item.qtd_questions && item.qtd_questions > 0) {
            moduleDone++;
            data += `<a href="#md${item.module_id}"><img src="./assets/img/sign.png" alt="Alfa Rupus Medalha"></a>`;
        }else{
            data += `<a href="#md${item.module_id}"><img src="./assets/img/sign.png" alt="Alfa Rupus Medalha" class="notViewed"></a>`;
        }
    });
    document.querySelector('.modulesSign').innerHTML = data;
    document.querySelector('#completeModules').innerHTML = `${moduleDone}/${json.data.qtd_modules} módulos concluídos`;
}
info();


async function loadModules() {
    let modules = await getModules();
    let userData = localStorage.getItem("userData")
    userData = JSON.parse(userData)
    if (modules.error) {
        localStorage.setItem('userData', null);
        window.location.href = 'login.html';
    }

    modules = modules.data;
    console.log(modules);
    let moduleTemplate = ``;
    for (const key in modules) {
        let module_id = modules[key].module.id;
        let title = modules[key].module.title;
        let module_status = modules[key].module.status;
        moduleTemplate += `
        <div class="module" id="md${module_id}">

            <h2>${title}</h2>
            <input type="hidden" name="module_id[]" value="${module_id}" />
            <div class="dropdownContainer">`;
                // console.log(modules[key])
                for (const key1 in modules[key].module_item) {
                    let title_item = modules[key].module_item[key1].title;
                    let type_item = modules[key].module_item[key1].type;
                    let url_item = modules[key].module_item[key1].url;
                    let url_capa = modules[key].module_item[key1].url_capa;
                    // console.log('module item id: ', modules[key].module_item[key1].id)

                    moduleTemplate += `
                    <div class="dropdownSingle dropdownHidden">

                        <div class="titleDropdown" onclick="toggleDrop(this)">

                            <h5>${title_item}</h5>

                            <svg width="13" height="9" viewBox="0 0 13 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M0 1.89309L1.37054 0.5L6.5 5.71383L11.6295 0.5L13 1.89309L6.5 8.5L0 1.89309Z"
                                    fill="rgba(44, 44, 44, 0.5)" />
                            </svg>

                        </div>`;

                        if (type_item == 'YouTube') {
                            moduleTemplate += `
                                <iframe width="560" height="315" src="${url_item.replace('watch?v=','embed/')}"
                                    title="YouTube video player" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            `;
                        }
                        if (type_item == 'File') {
                            moduleTemplate += `
                                <img src="${url_capa}" style="height:400px; width: auto; margin-bottom: 8px;background-size: auto;"/>
                                <div class="buttonsLastChd">
                                    <a href="${url_item}" target="_blank" class="seePDF">Visualizar Documento</a>
                                </div>
                            `;
                        }

                        if ((parseInt(key1) + 1) == modules[key].module_item.length) {
                            moduleTemplate += `<div class="buttonsLastChd">`;
                            if (module_status == true) {
                                moduleTemplate += `<a href="certificate.php?module=${module_id}&user_id=${userData.user_id}" target="_blank" class="seePDF">Visualizar Certificado</a>`;
                            }else{
                                moduleTemplate += `<button class="goToForm" onclick="fetchForm(${module_id});">Responder questionário</button>`;
                            }
                            moduleTemplate += `</div>`;
                        }
                    moduleTemplate += `</div>`;
                }


                moduleTemplate +=`

            </div>

        </div>`;
    }
    document.querySelector('.contentSection').innerHTML = moduleTemplate;
}
loadModules();