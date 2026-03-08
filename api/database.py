# =============================================
# database.py — Conexión a MySQL de XAMPP
# =============================================
import pymysql

def get_connection():
    return pymysql.connect(
        host="localhost",
        user="root",
        password="",
        database="veterinaria",
        cursorclass=pymysql.cursors.DictCursor
    )