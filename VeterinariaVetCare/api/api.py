# =============================================
# api.py — Endpoints de la API REST
# Lanzar con: uvicorn api:app --reload
# =============================================
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional
from database import get_connection
from core import calcular_estadisticas, validar_estado, formatear_fecha

app = FastAPI(title="VetCare API", version="1.0")

# CORS para que el index.php pueda hacer fetch
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Modelo de cita ────────────────────────────
class CitaCreate(BaseModel):
    id_paciente: int
    id_usuario: int
    fecha_hora: str
    motivo: str
    estado: Optional[str] = "pendiente"
    notas: Optional[str] = None

class CitaUpdate(BaseModel):
    fecha_hora: Optional[str] = None
    motivo: Optional[str] = None
    estado: Optional[str] = None
    notas: Optional[str] = None
    diagnostico: Optional[str] = None
    tratamiento: Optional[str] = None
    peso: Optional[float] = None

# ── GET /citas — Todas las citas ─────────────
@app.get("/citas")
def get_citas():
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("""
                SELECT c.*, p.nombre AS mascota, u.nombre_completo AS veterinario
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.activo = 1
                ORDER BY c.fecha_hora DESC
            """)
            citas = cursor.fetchall()
            # Formatear fechas para que sean serializables
            for cita in citas:
                cita["fecha_hora"] = formatear_fecha(cita["fecha_hora"])
            return citas
    finally:
        conn.close()

# ── GET /citas/{id} — Una cita ───────────────
@app.get("/citas/{id_cita}")
def get_cita(id_cita: int):
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("""
                SELECT c.*, p.nombre AS mascota, u.nombre_completo AS veterinario
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.id_cita = %s AND c.activo = 1
            """, (id_cita,))
            cita = cursor.fetchone()
            if not cita:
                raise HTTPException(status_code=404, detail="Cita no encontrada")
            cita["fecha_hora"] = formatear_fecha(cita["fecha_hora"])
            return cita
    finally:
        conn.close()

# ── POST /citas — Crear cita ─────────────────
@app.post("/citas", status_code=201)
def create_cita(cita: CitaCreate):
    conn = get_connection()
    try:
        # Validar estado con core.py
        estado = validar_estado(cita.estado)
        with conn.cursor() as cursor:
            cursor.execute("""
                INSERT INTO citas (id_paciente, id_usuario, fecha_hora, motivo, estado, notas, activo)
                VALUES (%s, %s, %s, %s, %s, %s, 1)
            """, (cita.id_paciente, cita.id_usuario, cita.fecha_hora,
                  cita.motivo, estado, cita.notas))
            conn.commit()
            nuevo_id = cursor.lastrowid
            return {"mensaje": "Cita creada correctamente", "id_cita": nuevo_id}
    finally:
        conn.close()

# ── PUT /citas/{id} — Modificar cita ─────────
@app.put("/citas/{id_cita}")
def update_cita(id_cita: int, datos: CitaUpdate):
    conn = get_connection()
    try:
        campos = []
        valores = []

        if datos.fecha_hora is not None:
            campos.append("fecha_hora = %s")
            valores.append(datos.fecha_hora)
        if datos.motivo is not None:
            campos.append("motivo = %s")
            valores.append(datos.motivo)
        if datos.estado is not None:
            estado = validar_estado(datos.estado)
            campos.append("estado = %s")
            valores.append(estado)
        if datos.notas is not None:
            campos.append("notas = %s")
            valores.append(datos.notas)
        if datos.diagnostico is not None:
            campos.append("diagnostico = %s")
            valores.append(datos.diagnostico)
        if datos.tratamiento is not None:
            campos.append("tratamiento = %s")
            valores.append(datos.tratamiento)
        if datos.peso is not None:
            campos.append("peso = %s")
            valores.append(datos.peso)

        if not campos:
            raise HTTPException(status_code=400, detail="No se enviaron datos para actualizar")

        valores.append(id_cita)
        with conn.cursor() as cursor:
            cursor.execute(
                f"UPDATE citas SET {', '.join(campos)} WHERE id_cita = %s AND activo = 1",
                valores
            )
            conn.commit()
            if cursor.rowcount == 0:
                raise HTTPException(status_code=404, detail="Cita no encontrada")
            return {"mensaje": "Cita actualizada correctamente"}
    finally:
        conn.close()

# ── DELETE /citas/{id} — Eliminar cita ───────
@app.delete("/citas/{id_cita}")
def delete_cita(id_cita: int):
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "UPDATE citas SET activo = 0 WHERE id_cita = %s",
                (id_cita,)
            )
            conn.commit()
            if cursor.rowcount == 0:
                raise HTTPException(status_code=404, detail="Cita no encontrada")
            return {"mensaje": "Cita eliminada correctamente"}
    finally:
        conn.close()

# ── GET /pacientes — Segunda tabla (extra x1.15) ──
@app.get("/pacientes")
def get_pacientes():
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("""
                SELECT p.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
                FROM pacientes p
                JOIN clientes c ON p.id_cliente = c.id_cliente
                WHERE p.activo = 1
                ORDER BY p.nombre
            """)
            return cursor.fetchall()
    finally:
        conn.close()

# ── GET /estadisticas — Core con pandas (extra x1.15) ──
@app.get("/estadisticas")
def get_estadisticas():
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT * FROM citas WHERE activo = 1")
            citas = cursor.fetchall()
            for cita in citas:
                cita["fecha_hora"] = formatear_fecha(cita["fecha_hora"])
            return calcular_estadisticas(citas)
    finally:
        conn.close()




        