from flask import Flask, render_template, request, redirect, url_for, session, flash, jsonify
import sqlite3


app = Flask(__name__)
app.secret_key = 'C0ntr4s3ñ4. d3- v10s'

def conectar_bd():
    return sqlite3.connect('\BD/GestorPresupuestos.db')

# Seccion principal
@app.route('/')
def index():

    conn = conectar_bd()
    cursor = conn.cursor()

    cursor.execute('SELECT * FROM Usuario WHERE email = ? AND password = ?', ('Itan.daniel.fr@gmail.com', '123'))
    user = cursor.fetchone()
    session['user_id'] = user[0]

    cursor.execute('SELECT * FROM Cuentas_de_banco WHERE ID_usuario = ?', (session['user_id'],))
    cuentas = cursor.fetchall()

    conn.close()
    return render_template('index.html', cuentas=cuentas)

# Añadir nuevo banco enlazado a Usuario
@app.route('/vincular_banco', methods=['GET', 'POST'])
def vincular_banco():
    if 'user_id' not in session:
        return redirect(url_for('login'))

    if request.method == 'POST':
        banco = request.form['banco']

        conn = conectar_bd()
        cursor = conn.cursor()
        cursor.execute('INSERT INTO Cuentas_de_banco (ID_usuario, banco) VALUES (?, ?)', (session['user_id'], banco))
        conn.commit()
        conn.close()

        flash('Cuenta de banco vinculada exitosamente.')
        return redirect(url_for('index'))

    return render_template('vicular_banco.html')

# Cerrar Sesion
@app.route('/logout')
def logout():
    session.pop('user_id', None)
    flash('Has cerrado sesión exitosamente.')
    return redirect(url_for('login'))

if __name__ == '__main__':
    app.run(debug=True)