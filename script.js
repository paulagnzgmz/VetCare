// =============================================
// script.js — VetCare
//
// Contiene:
//   1. Funciones del calendario (calendario.php)
//   2. Modales de citas (citas.php)
//   3. Modales de clientes (clientes.php)
//   4. Modales de pacientes (pacientes.php)
//   5. Modales de usuarios/admin (admin.php)
//
// Las funciones de DataTables se mantienen
// inline en cada PHP porque dependen del ID
// concreto de cada tabla.
//
// La variable "citas" la define calendario.php
// antes de cargar este script.
// =============================================


// ── 1. CALENDARIO ────────────────────────────

function formatearFecha(cadenaISO) {
    const fecha = new Date(cadenaISO);
    return fecha.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatearHora(cadenaISO) {
    const fecha = new Date(cadenaISO);
    return fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

function mostrarDetalle(cita) {
    document.getElementById("detalleVacio").style.display = "none";
    document.getElementById("detalleContenido").style.display = "flex";
    document.getElementById("detalleTitulo").textContent = cita.title;

    const etiqueta = document.getElementById("detalleEstado");
    etiqueta.textContent = cita.estado;
    etiqueta.className = "etiqueta etiqueta-" + cita.estado;

    let filas = "";
    filas += '<div class="detalle-fila"><span class="detalle-etiqueta">Fecha</span><span class="detalle-valor">' + formatearFecha(cita.start) + '</span></div>';
    filas += '<div class="detalle-fila"><span class="detalle-etiqueta">Hora</span><span class="detalle-valor">' + formatearHora(cita.start) + '</span></div>';
    filas += '<div class="detalle-fila"><span class="detalle-etiqueta">Mascota</span><span class="detalle-valor">' + cita.mascota + '</span></div>';
    filas += '<div class="detalle-fila"><span class="detalle-etiqueta">Especie</span><span class="detalle-valor">' + cita.especie + '</span></div>';
    filas += '<div class="detalle-fila"><span class="detalle-etiqueta">Cliente</span><span class="detalle-valor">' + cita.cliente + '</span></div>';
    filas += '<div class="detalle-fila"><span class="detalle-etiqueta">Veterinario</span><span class="detalle-valor">' + cita.veterinario + '</span></div>';

    document.getElementById("detalleFilas").innerHTML = filas;
}

function vaciarDetalle() {
    document.getElementById("detalleVacio").style.display = "";
    document.getElementById("detalleContenido").style.display = "none";
}

function iniciarCalendario() {
    const contenedor = document.getElementById("calendario");
    if (!contenedor) return; // solo se ejecuta en calendario.php

    const eventos = citas.map(cita => ({
        id:            cita.id,
        title:         cita.title,
        start:         cita.start,
        color:         cita.color,
        extendedProps: cita
    }));

    const calendario = new FullCalendar.Calendar(contenedor, {
        initialView: "dayGridMonth",
        locale: "es",
        headerToolbar: {
            left:   "prev,next today",
            center: "title",
            right:  "dayGridMonth,timeGridWeek,listWeek"
        },
        buttonText: {
            today: "Hoy",
            month: "Mes",
            week:  "Semana",
            list:  "Lista"
        },
        events: eventos,
        eventClick: function(info) {
            mostrarDetalle(info.event.extendedProps);
        },
        height: "auto"
    });

    calendario.render();
}


// ── 2. MODALES DE CITAS (citas.php) ──────────

function abrirModalCompletar(cita) {
    document.getElementById('completar_id_cita').value        = cita.id_cita;
    document.getElementById('completar_diagnostico').value    = cita.diagnostico || '';
    document.getElementById('completar_tratamiento').value    = cita.tratamiento || '';
    document.getElementById('completar_peso').value           = cita.peso || '';
    document.getElementById('completar_info').textContent     =
        cita.mascota + ' — ' + cita.motivo + ' — ' + cita.nombre_cliente + ' ' + cita.apellidos_cliente;
    document.getElementById('modalCompletarCita').classList.add('visible');
}

function cerrarModalCompletar() {
    document.getElementById('modalCompletarCita').classList.remove('visible');
}

function editarCita(cita) {
    document.getElementById('edit_id_cita').value      = cita.id_cita;
    document.getElementById('edit_id_paciente').value  = cita.id_paciente;
    document.getElementById('edit_id_usuario').value   = cita.id_usuario;

    const fechaHora = cita.fecha_hora.split(' ');
    document.getElementById('edit_fecha').value        = fechaHora[0];
    document.getElementById('edit_hora').value         = fechaHora[1].substring(0, 5);

    document.getElementById('edit_motivo').value       = cita.motivo;
    document.getElementById('edit_estado').value       = cita.estado;
    document.getElementById('edit_notas').value        = cita.notas || '';

    document.getElementById('modalEditarCita').classList.add('visible');
}

function cerrarModal() {
    document.getElementById('modalEditarCita').classList.remove('visible');
}


// ── 3. MODALES DE CLIENTES (clientes.php) ────

function editarCliente(cliente) {
    document.getElementById('edit_id_cliente').value  = cliente.id_cliente;
    document.getElementById('edit_nombre').value      = cliente.nombre;
    document.getElementById('edit_apellidos').value   = cliente.apellidos;
    document.getElementById('edit_telefono').value    = cliente.telefono || '';
    document.getElementById('edit_email').value       = cliente.email;
    document.getElementById('edit_direccion').value   = cliente.direccion || '';
    document.getElementById('modalEditarCliente').classList.add('visible');
}

function cerrarModalCliente() {
    document.getElementById('modalEditarCliente').classList.remove('visible');
}


// ── 4. MODALES DE PACIENTES (pacientes.php) ───

function editarPaciente(paciente) {
    document.getElementById('edit_id_paciente').value  = paciente.id_paciente;
    document.getElementById('edit_nombre_pac').value   = paciente.nombre;
    document.getElementById('edit_especie').value      = paciente.especie;
    document.getElementById('edit_raza').value         = paciente.raza || '';
    document.getElementById('edit_sexo').value         = paciente.sexo || '';
    document.getElementById('edit_fecha_nac').value    = paciente.fecha_nac || '';
    document.getElementById('edit_foto_actual').value  = paciente.foto || '';
    document.getElementById('modalEditarPaciente').classList.add('visible');
}

function cerrarModalPaciente() {
    document.getElementById('modalEditarPaciente').classList.remove('visible');
}


// ── 5. MODALES DE USUARIOS (admin.php) ───────

function editarUsuario(usuario) {
    document.getElementById('edit_id_usuario').value = usuario.id_usuario;
    document.getElementById('edit_nombre').value     = usuario.nombre_completo;
    document.getElementById('edit_email').value      = usuario.email;
    document.getElementById('edit_rol').value        = usuario.rol;
    document.getElementById('edit_password').value   = '';
    document.getElementById('modalEditarUsuario').classList.add('visible');
}

function cerrarModalUsuario() {
    document.getElementById('modalEditarUsuario').classList.remove('visible');
}


// ── 6. CERRAR MODALES AL HACER CLIC FUERA ────

window.addEventListener('click', function(event) {
    const modales = [
        { id: 'modalEditarCita',      fn: cerrarModal         },
        { id: 'modalCompletarCita',   fn: cerrarModalCompletar },
        { id: 'modalEditarCliente',   fn: cerrarModalCliente   },
        { id: 'modalEditarPaciente',  fn: cerrarModalPaciente  },
        { id: 'modalEditarUsuario',   fn: cerrarModalUsuario   }
    ];
    modales.forEach(function(m) {
        const el = document.getElementById(m.id);
        if (el && event.target === el) m.fn();
    });
});


// ── 7. INIT ───────────────────────────────────

document.addEventListener('DOMContentLoaded', function() {
    iniciarCalendario();
});
