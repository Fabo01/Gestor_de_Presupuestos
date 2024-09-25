import sqlite3
import os

def crearbd():
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    DATABASE = os.path.join(BASE_DIR, 'GestorPresupuestos.db')

    conn = sqlite3.connect(DATABASE)
    cursor = conn.cursor()  

    # Crear tabla de Cuentas de banco
    cursor.execute('''CREATE TABLE IF NOT EXISTS Cuentas_de_banco 
        (ID_cuentabanco INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_usuario INTEGER,
        banco TEXT)''')

    cursor.execute('''CREATE TABLE IF NOT EXISTS Usuario 
        (ID_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT UNIQUE,
        password TEXT,
        token_recuperacion TEXT)''')

    # Crear tabla de Presupuestos
    cursor.execute('''CREATE TABLE IF NOT EXISTS Presupuestos 
        (ID_Presupuesto INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_categoria INTEGER,
        gasto_mensual INTEGER,
        saldo_restante INTEGER,
        FOREIGN KEY (ID_categoria) REFERENCES Categoria(ID_categoria))''')

    # Crear tabla de Categorías
    cursor.execute('''CREATE TABLE IF NOT EXISTS Categoria 
        (ID_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_cuentabanco INTEGER,
        nombre TEXT)''')

    # Crear tabla de Transacciones
    cursor.execute('''CREATE TABLE IF NOT EXISTS Transacciones 
        (ID_trans INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_Cuentabanco INTEGER,
        ID_Categoria INTEGER,
        desc TEXT,
        fecha DATE,
        Monto INTEGER,
        FOREIGN KEY (ID_Cuentabanco) REFERENCES Cuentas_de_banco(ID_cuentabanco),
        FOREIGN KEY (ID_Categoria) REFERENCES Categoria(ID_categoria))''')

    conn.commit()
    conn.close()

def resetbd():
    
    conn = sqlite3.connect('BD/GestorPresupuestos.db')
    cursor = conn.cursor()

    cursor.execute('DELETE FROM Cuentas_de_banco')
    cursor.execute('DELETE FROM Usuario')
    cursor.execute('DELETE FROM Categoria')
    cursor.execute('DELETE FROM Presupuestos') #para los datos de las tablas al volver a guardar datos
    cursor.execute('DELETE FROM Transacciones')

    cursor.execute('DELETE FROM sqlite_sequence WHERE name="Cuentas_de_banco"')
    cursor.execute('DELETE FROM sqlite_sequence WHERE name="Usuario"')
    cursor.execute('DELETE FROM sqlite_sequence WHERE name="Categoria"')
    cursor.execute('DELETE FROM sqlite_sequence WHERE name="Presupuestos"')
    cursor.execute('DELETE FROM sqlite_sequence WHERE name="Transacciones"')     # para borrar los numeros de los autoincrement

    conn.commit()
    conn.close()

if __name__ == "__main__":
    crearbd()