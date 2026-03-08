<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>API Citas - VetCare</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=500">
    <style>
        .api-seccion {
            margin-bottom: 30px;
        }

        .api-seccion h2 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .campo-inline {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .campo-inline input,
        .campo-inline select {
            padding: 8px 12px;
            border: 1px solid #e3e1db;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
        }

        .campo-inline label {
            font-size: 12px;
            font-weight: 500;
            display: block;
            margin-bottom: 4px;
        }

        pre {
            background: #f8f7f4;
            border: 1px solid #e3e1db;
            border-radius: 8px;
            padding: 14px;
            font-size: 12px;
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        #tablaResultado {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        #tablaResultado th {
            background: #f8f7f4;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e3e1db;
        }

        #tablaResultado td {
            padding: 8px 12px;
            border-bottom: 1px solid #f0ede8;
        }
    </style>
</head>

<body>

    <aside class="menu-lateral">
        <div class="menu-logo">
            <img src="img/logo.png" alt="Logo VetCare" class="logo-img">
            Vet<span>Care</span>
        </div>
        <nav class="menu-nav">
            <a href="calendario.php" class="menu-enlace">Calendario</a>
            <a href="historial.php" class="menu-enlace">Historial clínico</a>
            <a href="citas.php" class="menu-enlace">Gestión de citas</a>
            <a href="pacientes.php" class="menu-enlace">Pacientes</a>
            <a href="clientes.php" class="menu-enlace">Clientes</a>
            <a href="admin.php" class="menu-enlace">Administración</a>
            <a href="api_citas.php" class="menu-enlace activo">API Citas</a>
        </nav>





    </aside>

    <div class="contenedor-principal">
        <main class="contenido">

            <div class="cabecera-pagina">
                <div>
                    <h1 class="titulo-pagina">API REST — Citas</h1>
                    <p class="subtitulo-pagina">Interfaz de prueba para los endpoints de la API</p>
                </div>
            </div>

            <!-- GET TODAS -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>GET /citas — Todas las citas</h2>
                    <button class="btn-accion btn-editar" onclick="getCitas()">Ejecutar</button>
                    <div style="margin-top:12px;">
                        <table id="tablaResultado"></table>
                    </div>
                </div>
            </div>

            <!-- GET UNA -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>GET /citas/{id} — Una cita</h2>
                    <div class="campo-inline">
                        <div>
                            <label>ID de la cita</label>
                            <input type="number" id="get_id" value="1" style="width:80px;">
                        </div>
                        <button class="btn-accion btn-editar" onclick="getCitaById()">Ejecutar</button>
                    </div>
                    <pre id="get_resultado">—</pre>
                </div>
            </div>

            <!-- POST -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>POST /citas — Crear nueva cita</h2>
                    <div class="campo-inline">
                        <div>
                            <label>ID Paciente</label>
                            <input type="number" id="post_paciente" value="1" style="width:80px;">
                        </div>
                        <div>
                            <label>ID Veterinario</label>
                            <input type="number" id="post_veterinario" value="3" style="width:80px;">
                        </div>
                        <div>
                            <label>Fecha y hora</label>
                            <input type="datetime-local" id="post_fecha">
                        </div>
                        <div>
                            <label>Motivo</label>
                            <input type="text" id="post_motivo" value="Revisión API" style="width:150px;">
                        </div>
                        <div>
                            <label>Estado</label>
                            <select id="post_estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                            </select>
                        </div>
                        <button class="btn-accion btn-completar" onclick="postCita()"
                            style="background:#16a34a;color:#fff;">Ejecutar</button>
                    </div>
                    <pre id="post_resultado">—</pre>
                </div>
            </div>

            <!-- PUT -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>PUT /citas/{id} — Modificar cita</h2>
                    <div class="campo-inline">
                        <div>
                            <label>ID de la cita</label>
                            <input type="number" id="put_id" value="1" style="width:80px;">
                        </div>
                        <div>
                            <label>Nuevo estado</label>
                            <select id="put_estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label>Nuevo motivo (opcional)</label>
                            <input type="text" id="put_motivo" style="width:150px;">
                        </div>
                        <button class="btn-accion btn-editar" onclick="putCita()">Ejecutar</button>
                    </div>
                    <pre id="put_resultado">—</pre>
                </div>
            </div>

            <!-- DELETE -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>DELETE /citas/{id} — Eliminar cita</h2>
                    <div class="campo-inline">
                        <div>
                            <label>ID de la cita</label>
                            <input type="number" id="delete_id" value="1" style="width:80px;">
                        </div>
                        <button class="btn-accion btn-eliminar" onclick="deleteCita()">Ejecutar</button>
                    </div>
                    <pre id="delete_resultado">—</pre>
                </div>
            </div>

            <!-- PACIENTES -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>GET /pacientes — Lista de pacientes</h2>
                    <button class="btn-accion btn-editar" onclick="getPacientes()">Ejecutar</button>
                    <pre id="pacientes_resultado">—</pre>
                </div>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="tarjeta api-seccion">
                <div class="tarjeta-cuerpo">
                    <h2>GET /estadisticas — Análisis con pandas</h2>
                    <button class="btn-accion btn-editar" onclick="getEstadisticas()">Ejecutar</button>
                    <pre id="estadisticas_resultado">—</pre>
                </div>
            </div>

        </main>
        <footer class="pie-pagina">
            <p>&copy; 2026 VetCare Clínica Veterinaria — Laredo, Cantabria</p>
        </footer>
    </div>

    <script>
        const API = "http://localhost:8000";

        // GET todas las citas — muestra tabla
        async function getCitas() {
            const res = await fetch(`${API}/citas`);
            const data = await res.json();
            const tabla = document.getElementById("tablaResultado");

            if (!data.length) { tabla.innerHTML = "<tr><td>Sin resultados</td></tr>"; return; }

            const cols = ["id_cita", "mascota", "veterinario", "fecha_hora", "motivo", "estado"];
            let html = "<thead><tr>" + cols.map(c => `<th>${c}</th>`).join("") + "</tr></thead><tbody>";
            data.forEach(fila => {
                html += "<tr>" + cols.map(c => `<td>${fila[c] ?? "-"}</td>`).join("") + "</tr>";
            });
            html += "</tbody>";
            tabla.innerHTML = html;
        }

        // GET una cita por ID
        async function getCitaById() {
            const id = document.getElementById("get_id").value;
            const res = await fetch(`${API}/citas/${id}`);
            const data = await res.json();
            document.getElementById("get_resultado").textContent = JSON.stringify(data, null, 2);
        }

        // POST crear cita
        async function postCita() {
            const body = {
                id_paciente: parseInt(document.getElementById("post_paciente").value),
                id_usuario: parseInt(document.getElementById("post_veterinario").value),
                fecha_hora: document.getElementById("post_fecha").value.replace("T", " ") + ":00",
                motivo: document.getElementById("post_motivo").value,
                estado: document.getElementById("post_estado").value
            };
            const res = await fetch(`${API}/citas`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            document.getElementById("post_resultado").textContent = JSON.stringify(data, null, 2);
        }

        // PUT modificar cita
        async function putCita() {
            const id = document.getElementById("put_id").value;
            const body = { estado: document.getElementById("put_estado").value };
            const motivo = document.getElementById("put_motivo").value;
            if (motivo) body.motivo = motivo;

            const res = await fetch(`${API}/citas/${id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            document.getElementById("put_resultado").textContent = JSON.stringify(data, null, 2);
        }

        // DELETE eliminar cita
        async function deleteCita() {
            const id = document.getElementById("delete_id").value;
            if (!confirm(`¿Eliminar la cita ${id}?`)) return;
            const res = await fetch(`${API}/citas/${id}`, { method: "DELETE" });
            const data = await res.json();
            document.getElementById("delete_resultado").textContent = JSON.stringify(data, null, 2);
        }

        // GET pacientes
        async function getPacientes() {
            const res = await fetch(`${API}/pacientes`);
            const data = await res.json();
            document.getElementById("pacientes_resultado").textContent = JSON.stringify(data, null, 2);
        }

        // GET estadísticas pandas
        async function getEstadisticas() {
            const res = await fetch(`${API}/estadisticas`);
            const data = await res.json();
            document.getElementById("estadisticas_resultado").textContent = JSON.stringify(data, null, 2);
        }
    </script>

</body>

</html>