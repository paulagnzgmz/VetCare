# =============================================
# core.py — Lógica de negocio con pandas
# Genera estadísticas a partir de las citas
# =============================================
import pandas as pd

def calcular_estadisticas(citas: list) -> dict:
    if not citas:
        return {"mensaje": "No hay citas para analizar"}

    df = pd.DataFrame(citas)

    # Contar citas por estado
    por_estado = df["estado"].value_counts().to_dict()

    # Contar citas por veterinario
    por_veterinario = df["id_usuario"].value_counts().to_dict()

    # Motivo más frecuente
    motivo_top = df["motivo"].value_counts().idxmax()

    # Total de citas
    total = len(df)

    return {
        "total_citas": total,
        "por_estado": por_estado,
        "por_veterinario": por_veterinario,
        "motivo_mas_frecuente": motivo_top
    }

def validar_estado(estado: str) -> str:
    """Normaliza el estado a minúsculas y valida que sea correcto"""
    estados_validos = ["pendiente", "confirmada", "completada", "cancelada"]
    estado_lower = estado.lower().strip()
    if estado_lower not in estados_validos:
        raise ValueError(f"Estado no válido. Debe ser uno de: {estados_validos}")
    return estado_lower

def formatear_fecha(fecha_hora) -> str:
    """Convierte datetime a string legible"""
    if fecha_hora is None:
        return ""
    return str(fecha_hora)