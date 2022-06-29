async function getModules() {
    let userData = localStorage.getItem('userData');
    userData = JSON.parse(userData);
    const req = await fetch(`${BASE_API}/module/fetch`, {
        method: 'GET',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + userData.JWT
        }
    });

    const json = await req.json();
    return json;
}