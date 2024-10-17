# *Gestor de Presupuestos*


## *Descripción General*

Gestor de Presupuestos es una aplicación diseñada para ayudar a los jóvenes a administrar mejor sus finanzas personales. La aplicación proporciona herramientas para registrar ingresos y gastos, establecer presupuestos específicos para diferentes categorías de gastos y recibir recomendaciones sobre cómo mejorar su salud financiera. También incentiva el buen manejo del dinero a través de logros y fomenta la interacción entre usuarios mediante una comunidad donde se pueden compartir artículos y comentarios sobre finanzas.


## *Funcionalidades Clave*

**Gestión de Transacciones:** Los usuarios pueden registrar transacciones financieras, que se clasifican como ingresos o gastos, y deben estar asociadas a una cuenta bancaria. Las categorías de tipo ingreso permiten registrar las fuentes de ingresos, mientras que las categorías de tipo gasto ayudan a organizar las salidas de dinero. La aplicación también permite editar o eliminar transacciones, garantizando que la información se mantenga precisa y actualizada.

**Cuentas de Banco:** Los usuarios pueden agregar y gestionar sus cuentas bancarias dentro de la aplicación. Cada transacción debe estar vinculada a una cuenta, lo cual permite llevar un registro detallado del saldo de cada cuenta. Esta funcionalidad ofrece una visión precisa del estado financiero del usuario, mostrando cómo las transacciones impactan en cada cuenta específica.

**Presupuestos por Categorías:** Las categorías de tipo gasto permiten a los usuarios definir un presupuesto mensual, lo cual ayuda a monitorear cuánto se ha gastado y cuánto queda disponible para cada categoría. Los ingresos no tienen presupuestos asignados, sino que se utilizan para calcular el saldo total disponible del usuario.

**Control de Gastos:** El sistema genera estadísticas y proporciona recomendaciones para ayudar a los usuarios a manejar mejor su dinero, identificando excesos o déficits en cada categoría de gasto.

**Logros:** La aplicación motiva a los usuarios a mejorar su salud financiera mediante logros que se desbloquean al cumplir con objetivos de ahorro y buen manejo del dinero.

**Validación de Datos:** La aplicación incluye validaciones para evitar errores como la duplicación de datos o la entrada incorrecta de información, asegurando que los datos sean fiables y coherentes.

**Panel de Control:** Un panel interactivo que proporciona a los usuarios una visión clara del estado de sus finanzas, mostrando gráficos y métricas clave que comparan ingresos y gastos por categoría.

**Perfil de Usuario:** Gestión de la información del perfil del usuario, permitiendo la personalización de la aplicación según sus necesidades financieras.

**Interacción con otros usuarios:** Los usuarios pueden compartir artículos relacionados con la salud financiera y comentar en los artículos de otros, creando un entorno comunitario para el aprendizaje y el apoyo mutuo.


## *Casos de Uso*

**Registrar una Transacción:** El usuario selecciona el tipo de transacción (ingreso o gasto), ingresa detalles como el monto, la descripción y la fecha, y la asocia a una categoría de ingreso o gasto. Las transacciones de tipo gasto se asignan a una cuenta bancaria y a una categoría con un presupuesto definido, ayudando al usuario a mantenerse dentro de los límites de gasto establecidos.

**Administrar Cuentas Bancarias:** El usuario puede agregar, editar o eliminar cuentas bancarias dentro de la aplicación. Cada transacción debe asociarse a una cuenta bancaria específica, lo cual permite un seguimiento detallado del saldo disponible en cada cuenta. Esto ayuda al usuario a tener un mayor control sobre dónde se encuentra su dinero y cómo se está utilizando.

**Gestionar Categorías de Ingreso y Gasto:** El usuario puede crear y modificar categorías de tipo ingreso y de tipo gasto. Las categorías de ingreso permiten organizar las diferentes fuentes de ingresos, mientras que las categorías de gasto ayudan a agrupar los gastos por tipo, como alimentación, transporte, entretenimiento, etc. Esto facilita la creación de presupuestos y la evaluación de los hábitos de gasto del usuario.

**Configurar un Presupuesto Mensual:** El usuario puede definir un presupuesto mensual para una categoría específica de gasto, asegurando que todos los gastos relacionados con esa categoría se organicen adecuadamente. Además, el usuario tiene la opción de asociar el presupuesto con una cuenta bancaria específica, asegurando que todos los gastos se descuenten de esa cuenta, lo cual permite un control más preciso sobre los fondos disponibles.

**Visualizar Resumen Financiero:** El usuario accede al panel de control para visualizar gráficos y métricas de sus ingresos y gastos, y comparar entre categorías. El resumen también muestra el saldo actual de cada cuenta bancaria y los movimientos registrados. Además, el usuario tiene la posibilidad de visualizar de manera separada las categorías de ingreso y las de gasto, facilitando una mejor comprensión de cómo administra sus finanzas y permitiendo filtrar transacciones por categoría para analizar en detalle los gastos e ingresos.

**Logros Financieros:** El usuario puede explorar los logros disponibles y monitorear su progreso hacia la obtención de nuevos logros. Estos logros se basan en buenas prácticas financieras, como ahorrar de forma constante durante un período o mantener los gastos dentro de los presupuestos asignados, motivando al usuario a mejorar sus hábitos financieros.

**Publicar un Artículo:** El usuario puede redactar y compartir artículos relacionados con la salud financiera, como sus experiencias o consejos de ahorro. Otros usuarios pueden interactuar a través de comentarios, creando un espacio para compartir ideas y aprender en comunidad.


## *Arquitectura del Proyecto*

### **La aplicación sigue una arquitectura cliente-servidor**:

**Front-End:** La interfaz de usuario está desarrollada con HTML, CSS y JavaScript, ofreciendo una experiencia interactiva y fácil de usar.

**Back-End:** El servidor está construido en PHP, encargándose de la lógica de la aplicación y la comunicación con la base de datos.

**Base de Datos:** Utiliza MySQL para almacenar información sobre usuarios, transacciones, categorías, logros, artículos y comentarios.

**Servidor:** La aplicación está alojada en el servidor Pillan de la universidad.


## *Modelo Entidad-Relación (MER)*

///  FOTO DE EL MER XD  ///


## *Requisitos del Sistema*

**PHP 7.4+**

**MySQL 5.7+**

**Servidor Pillan**

**Conexión a una base de datos en MySQL de la universidad**


## *Recursos Adicionales*

**Carta Gantt:** https://docs.google.com/spreadsheets/d/1mJLeKNzY8KoO_SFmw8oLuIio-cM1SNQPCZFzclRqnfk/edit?usp=sharing

**Diagrama de Interfaces:** https://www.figma.com/design/yRNXwqqGroWKTN1r0G3Clf/Untitled?node-id=0-1&t=3A49f1qMaVcWcjQT-1

**Link al Sitio Web:** https://pillan.inf.uct.cl/~marcelo.vidal/Gestor_de_Presupuestos/Traslado%20Flask%20a%20php/
