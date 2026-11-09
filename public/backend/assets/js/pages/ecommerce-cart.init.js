document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.querySelector('.page-content .row.mb-3 .col-xl-8'); // Contenedor de los productos en el carrito (lado izquierdo)
    const summaryItemsBody = document.getElementById('cart-summary-items'); // tbody donde listaremos los ítems en el resumen (lado derecho)
    const subtotalElement = document.getElementById('cart-subtotal');
    const discountElement = document.getElementById('cart-discount');
    const shippingElement = document.getElementById('cart-shipping');
    const taxElement = document.getElementById('cart-tax');
    const totalElement = document.getElementById('cart-total');

    // Función para obtener detalles de un producto a partir de su elemento HTML
    function getProductDetails(productElement) {
        const name = productElement.querySelector('.text-truncate a').innerText;
        const quantityInput = productElement.querySelector('.product-quantity');
        const quantity = parseInt(quantityInput.value);
        // Asegúrate de que el precio unitario se pueda obtener correctamente
        const priceText = productElement.querySelector('.product-price').innerText;
        const unitPrice = parseFloat(priceText.replace('$', '').trim());

        return {
            element: productElement,
            name: name,
            quantityInput: quantityInput,
            quantity: quantity,
            unitPrice: unitPrice
        };
    }

    // Función para actualizar un ítem en la tabla de resumen (lado derecho)
    function updateSummaryItem(productDetails) {
        let itemRow = summaryItemsBody.querySelector(`tr[data-product-name="${productDetails.name}"]`); // Busca la fila por nombre de producto

        if (productDetails.quantity > 0) {
            const itemSubtotal = productDetails.quantity * productDetails.unitPrice;

            if (!itemRow) {
                // Si la fila no existe, crearla
                itemRow = document.createElement('tr');
                itemRow.setAttribute('data-product-name', productDetails.name); // Añadir identificador
                itemRow.innerHTML = `
                    <td>${productDetails.name}</td>
                    <td class="text-end summary-item-quantity">${productDetails.quantity}</td>
                    <td class="text-end summary-item-price">$${productDetails.unitPrice.toFixed(2)}</td>
                    <td class="text-end summary-item-subtotal">$${itemSubtotal.toFixed(2)}</td>
                `;
                summaryItemsBody.appendChild(itemRow);
            } else {
                // Si la fila existe, actualizarla
                itemRow.querySelector('.summary-item-quantity').innerText = productDetails.quantity;
                itemRow.querySelector('.summary-item-subtotal').innerText = `$${itemSubtotal.toFixed(2)}`;
            }
        } else {
            // Si la cantidad es 0, eliminar la fila si existe
            if (itemRow) {
                summaryItemsBody.removeChild(itemRow);
            }
        }
    }

    // Función para recalcular y actualizar los totales generales
    function updateOverallTotals() {
        let currentSubtotal = 0;
        summaryItemsBody.querySelectorAll('tr[data-product-name]').forEach(itemRow => {
            const itemSubtotalText = itemRow.querySelector('.summary-item-subtotal').innerText;
            currentSubtotal += parseFloat(itemSubtotalText.replace('$', '').trim());
        });

        // Actualiza el Sub Total
        subtotalElement.innerText = `$ ${currentSubtotal.toFixed(2)}`;

        // Aquí deberías añadir la lógica para calcular y actualizar el descuento, envío e impuestos
        // usando 'currentSubtotal' como base.
        // Por ejemplo:
        // const discount = currentSubtotal * 0.15; // Ejemplo: 15% de descuento
        // discountElement.innerText = `- $ ${discount.toFixed(2)}`;
        // ... y así sucesivamente para envío y impuestos.

        // Calcula y actualiza el Total (Sub Total - Descuento + Envío + Impuestos)
        // const total = currentSubtotal - discount + shipping + tax; // Ejemplo
        // totalElement.innerText = `$${total.toFixed(2)}`;

        // Placeholder for overall total calculation (replace with your actual logic)
        // For simplicity, let's assume Total is just Sub Total for now.
        // You need to implement the logic for discount, shipping, tax based on your template's requirements.
        totalElement.innerText = `$${currentSubtotal.toFixed(2)}`; // **REPLACE WITH ACTUAL TOTAL CALCULATION**
    }


    // Inicializar el resumen con los productos que ya están en el HTML al cargar la página
    cartItemsContainer.querySelectorAll('.card.product').forEach(productElement => {
        const productDetails = getProductDetails(productElement);
        if (productDetails.quantity > 0) {
             updateSummaryItem(productDetails);
        }
    });
     updateOverallTotals(); // Calcular totales iniciales


    // **NUEVA LÓGICA PARA BOTONES + y -**
    cartItemsContainer.querySelectorAll('.input-step button').forEach(button => {
        button.addEventListener('click', function() {
            const quantityInput = this.closest('.input-step').querySelector('.product-quantity');

            // Espera un pequeño momento para que la lógica original de la plantilla actualice el input
            setTimeout(() => {
                // Dispara manualmente el evento 'change' en el input
                const event = new Event('change');
                quantityInput.dispatchEvent(event);
            }, 50); // Pequeño retraso (50ms suele ser suficiente)
        });
    });

    // Mantener el listener en el input en caso de que el valor se cambie manualmente
    cartItemsContainer.querySelectorAll('.product-quantity').forEach(quantityInput => {
        quantityInput.addEventListener('change', function() {
            const productElement = this.closest('.card.product');
            const productDetails = getProductDetails(productElement);
            updateSummaryItem(productDetails);
            updateOverallTotals(); // Recalcular totales después de actualizar un ítem
        });
    });

});