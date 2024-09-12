function escribirotrobanco() {
    const banco = document.getElementById("banco");
    const otrobancocontenedor = document.getElementById("otrobancocontenedor");

    console.log(banco.value);

    if (banco.value === "otro") {
        otrobancocontenedor.style.display = "block";
    } else {
        otrobancocontenedor.style.display = "none";
    }
}

document.getElementById('crearcuenta').addEventListener('submit', function(event) {
    event.preventDefault();

    const nombrecuenta = document.getElementById('nombrecuenta').value;
    let banco = document.getElementById('banco').value;
    const otrobanco = document.getElementById('otrobanco').value;
    const tipocuenta = document.getElementById('tipocuenta').value;
    // const moneda = document.getElementById('moneda').value;

    if (banco === 'otro' && otrobanco.trim() !== '') {// se rescatan los datos y se guardan en las variables, otrobanco escrito por el usuario se reemplaza por banco
        banco = otrobanco;                            // asi el banco es el escrito por el usuario
    }
});                     