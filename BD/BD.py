import sqlite3

def crearbd():
    conn = sqlite3.connect('BD/GestorPresupuestos.db')
    cursor = conn.cursor()  

    # Crear tabla de Cuentas de banco
    cursor.execute('''CREATE TABLE IF NOT EXISTS Cuentas_de_banco 
        (ID_cuentabanco INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        banco TEXT)''')

    cursor.execute('''CREATE TABLE IF NOT EXISTS Usuario 
        (ID_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_cuentabanco INTEGER,
        name TEXT,
        password TEXT,
        fecha_registro DATE,
        FOREIGN KEY (ID_cuentabanco) REFERENCES Cuentas_de_banco(ID_cuentabanco))''')

    # Crear tabla de Presupuestos
    cursor.execute('''CREATE TABLE IF NOT EXISTS Presupuestos 
        (ID_Presupuesto INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_categoria INTEGER,
        gasto_mensual INTEGER,
        FOREIGN KEY (ID_categoria) REFERENCES Categoria(ID_categoria))''')

    # Crear tabla de Categorías
    cursor.execute('''CREATE TABLE IF NOT EXISTS Categoria 
        (ID_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
        ID_Presupuesto INTEGER,
        nombre TEXT,
        tipo TEXTL,
        FOREIGN KEY (ID_Presupuesto) REFERENCES Presupuestos(ID_Presupuesto))''')

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

def insertarbd():
    conn = sqlite3.connect('BD/GestorPresupuestos.db')
    cursor = conn.cursor() 

    cursor.execute('''INSERT INTO Cuentas_de_banco (name, banco)
        VALUES ('Cuenta Personal', 'Banco Nacional')''')

    # Insertar un usuario
    cursor.execute('''INSERT INTO Usuario (ID_cuentabanco, name, password, fecha_registro)
        VALUES (1, 'Juan Perez', '1234', '2024-09-07')''')

    # Insertar una categoría
    cursor.execute('''INSERT INTO Categoria (nombre, tipo)
        VALUES ('Alimentos', 'Egreso')''')

    # Insertar un presupuesto para esa categoría
    cursor.execute('''INSERT INTO Presupuestos (ID_categoria, gasto_mensual)
        VALUES (1, 60000)''')

    # Insertar una transacción
    cursor.execute('''INSERT INTO Transacciones (ID_Cuentabanco, ID_Categoria, desc, fecha, Monto)
        VALUES (1, 1, 'Compra supermercado', '2024-09-07', 20000)''')

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
