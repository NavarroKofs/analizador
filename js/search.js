document.getElementById("btn").addEventListener("click", search);

function search() {
    let palabraBuscar = document.getElementById("inputSearch").value;
    if(!palabraBuscar) {
        alert("Introduce una palabra en el buscador")
        return;
    }
    let palabraDecodificada = removeAccents(palabraBuscar.replace(/ /g,"_"));
    let urlBusqueda = 'buscador.php?consulta=' + palabraDecodificada;
    get(urlBusqueda).then(function(response) {
        alert(response);
    }, function(error) {
        alert("Se ha producido un error, intente más tarde.")
    })
}

function get(url) {
    return new Promise(function(resolve, reject) {
        var req = new XMLHttpRequest();
        req.open('GET', url);
        req.onload = function() {
            if (req.status == 200) {
                resolve(req.response);
            }
            else {
                reject(Error(req.statusText));
            }
        };
        req.onerror = function() {
        reject(Error("Network Error"));
        };
        req.send();
    });
}

const removeAccents = (str) => {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}