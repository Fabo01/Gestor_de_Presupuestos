import os
import sqlite3
from flask import Flask, jsonify, request, render_template

app = Flask(__name__)

# Ruta absoluta a la base de datos
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATABASE = os.path.join(BASE_DIR, '..', 'BD', 'GestorPresupuestos.db')

# Función para conectar a la base de datos
def conectar_bd():
    conn = sqlite3.connect(DATABASE)
    conn.row_factory = sqlite3.Row  # Permitir acceso a las columnas por nombre
    return conn

# Ruta para servir la página principal de categorías
@app.route('/')
def index():
    return render_template('categorias.html')

# Ruta para obtener todas las categorías
@app.route('/categorias', methods=['GET'])
def obtener_categorias():
    conn = conectar_bd()
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM Categoria')
    categorias = cursor.fetchall()
    conn.close()

    lista_categorias = []
    for categoria in categorias:
        lista_categorias.append({
            'id_categoria': categoria['ID_categoria'],
            'id_presupuesto': categoria['ID_Presupuesto'],
            'nombre': categoria['nombre'],
            'tipo': categoria['tipo']
        })

    return jsonify(lista_categorias)

# Ruta para agregar una nueva categoría
@app.route('/categorias', methods=['POST'])
def agregar_categoria():
    nueva_categoria = request.json
    nombre = nueva_categoria.get('nombre')
    tipo = nueva_categoria.get('tipo')
    id_presupuesto = nueva_categoria.get('id_presupuesto')

    if not nombre or not tipo or not id_presupuesto:
        return jsonify({'mensaje': 'Todos los campos son obligatorios'}), 400

    conn = conectar_bd()
    cursor = conn.cursor()

    cursor.execute('''INSERT INTO Categoria (ID_Presupuesto, nombre, tipo)
                      VALUES (?, ?, ?)''', (id_presupuesto, nombre, tipo))
    conn.commit()
    conn.close()

    return jsonify({'mensaje': 'Categoría agregada exitosamente'}), 201

# Ruta para modificar una categoría existente
@app.route('/categorias/<int:id_categoria>', methods=['PUT'])
def modificar_categoria(id_categoria):
    datos_categoria = request.json
    nombre = datos_categoria.get('nombre')
    tipo = datos_categoria.get('tipo')
    id_presupuesto = datos_categoria.get('id_presupuesto')

    if not nombre or not tipo or not id_presupuesto:
        return jsonify({'mensaje': 'Todos los campos son obligatorios'}), 400

    conn = conectar_bd()
    cursor = conn.cursor()

    cursor.execute('''UPDATE Categoria 
                      SET nombre = ?, tipo = ?, ID_Presupuesto = ?
                      WHERE ID_categoria = ?''', (nombre, tipo, id_presupuesto, id_categoria))
    conn.commit()
    conn.close()

    return jsonify({'mensaje': 'Categoría modificada exitosamente'}), 200

# Ruta para eliminar una categoría por ID
@app.route('/categorias/<int:id_categoria>', methods=['DELETE'])
def eliminar_categoria(id_categoria):
    conn = conectar_bd()
    cursor = conn.cursor()

    # Verifica si la categoría existe antes de intentar eliminarla
    cursor.execute('SELECT * FROM Categoria WHERE ID_categoria = ?', (id_categoria,))
    categoria = cursor.fetchone()

    if categoria is None:
        conn.close()
        return jsonify({'mensaje': 'Categoría no encontrada'}), 404  # Error si no existe

    # Elimina la categoría si existe
    cursor.execute('DELETE FROM Categoria WHERE ID_categoria = ?', (id_categoria,))
    conn.commit()
    conn.close()

    return jsonify({'mensaje': 'Categoría eliminada exitosamente'}), 200

if __name__ == '__main__':
    app.run(debug=True)
