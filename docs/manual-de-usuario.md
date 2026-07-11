# Manual de Usuario — Sistema de Gestión Hotelera Wyndham Manta

**Versión:** 1.0.0  
**Última actualización:** Julio 2026  
**Hotel:** Wyndham Manta

---

## Índice

1. [Introducción](#1-introducción)
2. [Acceso al sistema](#2-acceso-al-sistema)
3. [Panel de Inicio](#3-panel-de-inicio)
4. [Módulo Cocina](#4-módulo-cocina)
5. [Módulo Médico](#5-módulo-médico)
6. [Reportes](#6-reportes)
7. [Solución de problemas](#7-solución-de-problemas)
8. [Preguntas frecuentes](#8-preguntas-frecuentes)

---

## 1. Introducción

### 1.1 ¿Qué es este sistema?

Es una plataforma web interna del Hotel Wyndham Manta que permite:

- **Departamento de Cocina**: importar datos de consumo de alimentos desde archivos Excel y visualizar estadísticas.
- **Departamento Médico**: registrar atenciones médicas, gestionar pacientes, controlar inventario de medicinas e insumos, y generar reportes.

### 1.2 ¿Quiénes lo usan?

| Rol | Uso principal |
|-----|---------------|
| Personal de cocina | Subir datos de consumo, ver reportes |
| Médicos / Enfermeros | Registrar atenciones, recetar medicación |
| Administrativos médicos | Gestionar pacientes, catálogos, inventario |
| Supervisores | Generar reportes, revisar estadísticas |

---

## 2. Acceso al sistema

### 2.1 Ingresar

1. Abre tu navegador web (Chrome, Firefox, Edge).
2. Ve a la dirección del sistema (ej: `http://wyndham-manta.local/admin` o la URL que te hayan proporcionado).
3. Verás la pantalla de inicio de sesión:

![Pantalla de login]
- Fondo: imagen del hotel
- Tarjeta blanca translúcida con el logo
- Campos: **Correo electrónico** y **Contraseña**

### 2.2 Credenciales

- **Usuario administrador:** `admin@gmail.com`
- **Contraseña:** `admin123`

> ⚠️ **Importante:** Cambia la contraseña después del primer inicio de sesión.

### 2.3 Recuperar acceso

Si olvidaste tu contraseña, contacta al administrador del sistema para restablecerla.

### 2.4 Cerrar sesión

1. Haz clic en el botón **Cerrar sesión** (icono de puerta con flecha) en la esquina superior derecha.
2. Confirma en la ventana emergente.

### 2.5 Modo oscuro / claro

1. Haz clic en el botón circular (sol/luna) en la barra superior.
2. El tema se guarda automáticamente para tu próxima visita.

---

## 3. Panel de Inicio

Al ingresar, verás el **Panel de Inicio** con un resumen general:

- **Total archivos importados** (Cocina)
- **Total consumos registrados** (Cocina)
- **Total productos en catálogo** (Cocina)

---

## 4. Módulo Cocina

### 4.1 Dashboard de Cocina

**Menú:** Cocina → Dashboard

![Dashboard Cocina]

En esta pantalla encontrarás:

#### Indicadores principales (tarjetas superiores)
- **Registros última fecha**: cuántos productos se consumieron en el último registro
- **Productos usados**: variedad de productos distintos en esa fecha
- **Fechas registradas**: cuántos días diferentes tienen datos
- **Total registros**: todos los registros históricos
- **Productos**: total de productos en el catálogo
- **Archivos importados**: total de archivos subidos

#### Consumo del día
1. Usa el selector de fecha para elegir un día.
2. La tabla se actualiza mostrando los productos consumidos, agrupados por tipo de unidad (kilos, litros, porciones, gramos, unidades).

#### Top 10 productos
Lista de los 10 productos más consumidos históricamente.

#### Recomendación (herramienta de planificación)
Esta herramienta te ayuda a calcular cuánto comprar basado en la ocupación del hotel:

1. Selecciona una **fecha de referencia** (un día típico).
2. Ingresa cuántos **huéspedes había** ese día.
3. Ingresa cuántos **huéspedes esperas** para el evento/día que planeas.
4. El sistema calculará las cantidades sugeridas de cada producto.

#### Gráficos
- **Productos más consumidos**: gráfico de barras con el top 10.
- **Consumo por servicio**: distribución entre desayuno, almuerzo y cena.

---

### 4.2 Subir Datos de Cocina

**Menú:** Cocina → Subir datos

Aquí puedes importar archivos Excel con los consumos diarios.

#### Paso 1: Seleccionar archivo
1. Haz clic en **Seleccionar archivo**.
2. Elige un archivo Excel (`.xlsx`, `.xls`) o CSV.
3. Peso máximo: 10 MB.

#### Paso 2: Cargar
1. Haz clic en **Subir datos**.
2. El archivo se guarda en el sistema con estado "recibido".

#### Paso 3: Previsualizar (opcional)
1. En la tabla **Archivos subidos recientemente**, haz clic en **Previsualizar**.
2. Se mostrarán las primeras 20 filas para que verifiques que los datos se leyeron correctamente.

#### Paso 4: Procesar
1. Haz clic en **Procesar archivo**.
2. El sistema leerá el archivo, detectará automáticamente las columnas (fecha, producto, cantidad) y creará los registros.
3. Verás un resumen: filas importadas, errores y duplicados.

> ✅ **Tip:** Los encabezados deben incluir al menos "Fecha" y "Cantidad". El resto de columnas se detectan automáticamente por nombre.

#### ¿Qué pasa si hay errores?
- Las filas con problemas se registran como errores.
- Puedes ver los errores en la sección **Reportes** de Cocina.
- El archivo quedará marcado como "procesado con errores" si algunas filas fallaron.

---

### 4.3 Reportes de Cocina

**Menú:** Cocina → Reportes

#### Descargar reportes
Puedes descargar reportes en formato Excel:

| Opción | Qué incluye |
|--------|-------------|
| **Descargar día** | Elige una fecha → descarga Excel con productos y cantidades |
| **Descargar rango** | Elige fecha inicio y fin → descarga Excel detallado |
| **Descargar todo** | Todos los registros históricos |

#### Semanas consumidas
Tabla resumen que muestra por semana: fecha inicio, fecha fin, total registros y productos distintos.

#### Errores de importación
Lista los errores ocurridos al procesar archivos, con el número de fila y el mensaje de error.

---

## 5. Módulo Médico

### 5.1 Dashboard de Médico

**Menú:** Médico → Dashboard

#### Indicadores principales
- **Total atenciones**: todas las consultas registradas
- **Total pacientes**: personas atendidas
- **Total Kardex**: registros de inventario
- **Última fecha**: el día más reciente con datos
- **Atenciones hoy**: atenciones en el último día registrado
- **Días cubiertos**: cuántos días diferentes tienen atenciones

#### Resumen del mes
1. Selecciona un mes del selector.
2. Verás: atenciones, pacientes distintos, días con atención, atenciones con certificado, medicamentos distintos usados.

#### Atenciones del día
1. Selecciona una fecha.
2. Se listan todas las atenciones de ese día ordenadas por nombre del paciente.
3. Cada fila muestra: nombre, edad, área, cargo, causa, diagnóstico, tipo de certificado, medicación.

#### Alertas de stock
Muestra productos de inventario con saldo igual o menor a 2 unidades. Útil para saber qué necesita reposición urgente.

#### Gráficos
- **Atenciones mensuales**: línea con el acumulado por mes del año actual.
- **Distribución de causas**: pastel con los motivos de atención más frecuentes.
- **Atenciones por área**: barras comparativas entre departamentos.
- **Medicamentos más usados**: ranking de medicación.

---

### 5.2 Partes Diarios (Atenciones Médicas)

**Menú:** Médico → Partes diarios

Esta es la pantalla principal del módulo médico. Aquí registras cada atención que realizas.

#### Vista general
- La tabla muestra las atenciones del día actual por defecto.
- Puedes cambiar a **Ver todo** para ver un rango de fechas más amplio.
- Usa los **filtros** para refinar la búsqueda.

#### Registrar una nueva atención

1. Haz clic en **Nueva atención** (botón azul).
2. Se abrirá un modal con el formulario.

**Paso 1: Buscar paciente**
- Escribe el nombre, cédula o área en el buscador.
- El sistema te sugerirá pacientes existentes.
- Si el paciente no existe, haz clic en **➕ Crear paciente** para registrarlo rápidamente.

**Paso 2: Datos de la atención**
| Campo | Descripción |
|-------|-------------|
| Fecha | Fecha de la atención (por defecto hoy) |
| Tipo paciente | Colaborador, Aspirante, Externo, Huésped |
| Habitación | Solo para huéspedes |
| Turno | Mañana, Tarde, Noche |
| Área | Departamento del paciente |
| Cargo | Puesto de trabajo |
| Causa | Motivo de la atención (obligatorio) |
| Diagnóstico | Desde el catálogo (escribe ≥ 2 letras para buscar) |

**Paso 3: Certificado médico (opcional)**
Si el paciente requiere certificado:
1. Marca un tipo de certificado: REPOSO, SUBSIDIO, ALTA MÉDICA, etc.
2. Elige la entidad que certifica.
3. Indica horas o días de descanso.
4. Fechas de inicio y fin.
5. Médico que certifica.

**Paso 4: Medicación**
1. Haz clic en **➕ Agregar medicamento**.
2. Selecciona el medicamento y la cantidad.
3. Puedes agregar varias líneas.

**Paso 5: Tipo de salida e incidente**
- **Tipo de salida**: ALTA, REPOSO, DERIVACIÓN, OBSERVACIÓN, HOSPITALIZACIÓN.
- **Incidente**: solo si aplica (caída, corte, quemadura, etc.).

**Paso 6: Guardar**
- Haz clic en **Guardar**.
- El sistema registra la atención Y descuenta automáticamente los medicamentos del inventario.

#### Editar una atención
1. Busca la atención en la tabla.
2. Haz clic en el botón **Editar** (icono de lápiz).
3. Se abrirá el modal con todos los datos cargados.
4. Realiza los cambios y haz clic en **Guardar**.

#### Eliminar una atención
1. Haz clic en el botón **Eliminar** (icono de papelera).
2. Confirma la eliminación en la ventana emergente.
3. El sistema eliminará la atención Y revertirá los movimientos de inventario asociados.

#### Filtros
| Filtro | Cómo usarlo |
|--------|-------------|
| Solo hoy / Ver todo | Alterna entre el día actual y un rango |
| Buscar texto | Escribe nombre, observación o habitación |
| Área | Filtra por departamento |
| Causa | Filtra por motivo de atención |
| Tipo paciente | Colaborador, huésped, etc. |
| Estado | Con certificado o sin certificado |

---

### 5.3 Pacientes

**Menú:** Médico → Pacientes

Gestión completa del directorio de pacientes.

#### Lista de pacientes
- Tabla con todos los pacientes, ordenados por estado (activos primero) y alfabéticamente.
- Cada fila muestra: nombre, cédula, edad, área, cargo, tipo, exámenes, estado.

#### Registrar un nuevo paciente
1. Haz clic en **Nuevo paciente**.
2. Completa los campos obligatorios (nombre).
3. Opcionalmente completa: cédula, edad, área, cargo, fecha de ingreso, teléfono.
4. En la pestaña **Exámenes**, registra las fechas de: espirometría, ecografía, audiometría, optometría.
5. En la pestaña **Visitas**, registra visitas anuales (2021-2026).
6. En **Historial**, puedes agregar: patologías, vacunas, fichas anteriores, antecedentes, observaciones.
7. Haz clic en **Guardar**.

#### Editar paciente
1. Haz clic en el nombre del paciente o en el botón **Editar**.
2. Modifica los campos necesarios.
3. Haz clic en **Guardar**.

#### Ver detalle de paciente
Haz clic en la fila del paciente para expandir su información completa, incluyendo:
- Datos personales
- Estado de exámenes (vigente, vencido o pendiente)
- Visitas por año

#### Activar / Desactivar paciente
- Usa el botón de alternancia (check/tachado) para activar o desactivar un paciente.
- Los pacientes inactivos no aparecen en los buscadores de partes diarios.

#### Buscar y filtrar
| Filtro | Descripción |
|--------|-------------|
| Buscar | Por nombre, cédula o área |
| Área | Departamento específico |
| Tipo | Colaborador, aspirante, externo, paciente, huésped |
| Estado | Activos o inactivos |

---

### 5.4 Base Médica (Catálogos)

**Menú:** Médico → Base médica

Aquí se gestionan los catálogos que alimentan los formularios del sistema.

#### Catálogos disponibles

| Catálogo | Ejemplos de registros |
|----------|----------------------|
| **Áreas** | Cocina, Limpieza, Recepción, Mantenimiento |
| **Cargos** | Chef, Botones, Recepcionista, Doctor |
| **Causas** | Dolor de cabeza, Fiebre, Accidente laboral |
| **Diagnósticos** | Lista extensa de diagnósticos médicos |
| **Entidades certificado** | IESS, MSP, Privado, Municipal |

#### Cómo usar
1. Selecciona el tipo de catálogo en las pestañas superiores.
2. Verás la lista de registros con su estado (activo/inactivo).
3. Para **agregar**: haz clic en "Nuevo [nombre del catálogo]".
4. Para **editar**: haz clic en el lápiz junto al registro.
5. Para **desactivar/reactivar**: usa el botón de alternancia.
6. Para **eliminar**: haz clic en la papelera y confirma.

> ⚠️ **Precaución:** Al eliminar un registro que esté siendo usado en atenciones, podrías perder referencias. Es mejor desactivarlo en lugar de eliminarlo.

---

### 5.5 Inventario Médico

**Menú:** Médico → Inventario

Control de medicinas e insumos del dispensario médico.

#### Productos
La tabla principal muestra:
- Nombre del producto
- Tipo (medicina / insumo)
- Saldo actual
- Stock mínimo
- Estado

#### Registrar un nuevo producto
1. Haz clic en **Nuevo producto**.
2. Selecciona el **tipo**: Medicina o Insumo.
3. Escribe el **nombre** (se normaliza a mayúsculas automáticamente).
4. Opcional: stock mínimo, stock inicial (si es nuevo), fecha de caducidad.
5. Haz clic en **Guardar**.
6. El sistema sincroniza automáticamente el producto con el catálogo de medicamentos.

#### Registrar un movimiento
1. Selecciona un producto de la lista.
2. En el panel de **Registrar movimiento**:
   - **Tipo**: Ingreso (llega mercadería), Salida (se consume/dispensa), Ajuste (corrección).
   - **Cantidad**: usa positivo para ingresos, negativo para ajustes a la baja.
   - **Responsable** y **Observación** (opcional).
3. Haz clic en **Guardar movimiento**.

#### Ver detalle de producto
Haz clic en un producto para ver:
- Saldo actual
- Stock mínimo
- Fecha de caducidad
- Últimos 20 movimientos

#### Filtros
- **Buscar**: por nombre del producto.
- **Tipo**: todos, medicinas o insumos.
- **Estado**: activos, inactivos o todos.

#### Stock bajo
El contador "Stock bajo" muestra cuántos productos tienen saldo igual o menor al mínimo configurado.

---

### 5.6 Kardex Mensual

**Menú:** Médico → Kardex mensual

> **Nota:** El Kardex mensual se encuentra integrado en la página de Reportes Médico.

Consulta el detalle en la sección [6.2 Reportes Médico](#62-reportes-médico).

---

## 6. Reportes

### 6.1 Reportes de Cocina

**Menú:** Cocina → Reportes

#### Reportes descargables

| Reporte | Contenido | Formato |
|---------|-----------|---------|
| Consumo por día | Producto, unidad, cantidad | Excel (.xlsx) |
| Consumo por rango | Fecha, producto, unidad, cantidad | Excel (.xlsx) |
| Consumo general | Todos los registros | Excel (.xlsx) |

#### Cómo descargar
1. Para **un día específico**: busca la fecha en el calendario y haz clic en **Descargar**.
2. Para **un rango**: selecciona fecha inicio y fin, luego haz clic en **Descargar rango**.
3. Para **todo**: haz clic en **Descargar todo**.

#### Resumen semanal
La tabla "Semanas consumidas" te da una vista rápida de cada semana: inicio, fin, total registros y productos.

---

### 6.2 Reportes Médico

**Menú:** Médico → Reportes

#### Reportes descargables

| Reporte | Contenido | Formato |
|---------|-----------|---------|
| Parte diario por día | Nombres, área, cargo, causa, diagnóstico, certificado, medicación | Excel (.xlsx) |
| Parte diario por rango | Todos los campos completos (16 columnas) | Excel (.xlsx) |
| Parte diario general | Todas las atenciones | Excel (.xlsx) |
| Kardex completo | Periodo, tipo, nombre, saldos, caducidad | Excel (.xlsx) |
| Kardex + movimientos | Resumen + hoja de movimientos detallados | Excel (.xlsx, 2 hojas) |
| Kardex mensual | Un libro por mes con estadísticas | Excel (.xlsx, varias hojas) |

#### Generar Kardex mensual en pantalla
1. En la sección **Kardex mensual**:
   - Selecciona un mes o define un rango de fechas.
   - Haz clic en **Generar Kardex**.
2. El sistema calculará en pantalla: producto, saldo anterior, ingresos, egresos, total.
3. Puedes **Cerrar** el kardex para evitar modificaciones accidentales.
4. Haz clic en **Exportar a Excel** para descargarlo.

#### Historial de Kardex
- La tabla "Meses disponibles" muestra un resumen por mes: atenciones, pacientes, certificados, causa principal, área principal, medicamentos usados.
- Haz clic en un mes para cargarlo en el generador.

#### Cómo descargar un reporte
1. Selecciona el tipo de reporte (día, rango, kardex, etc.).
2. Para reportes por rango: elige fecha inicio y fin.
3. Haz clic en el botón de descarga correspondiente.
4. El archivo Excel se descargará automáticamente.

---

## 7. Solución de problemas

### 7.1 No puedo iniciar sesión

| Posible causa | Solución |
|---------------|----------|
| Credenciales incorrectas | Verifica que el correo y contraseña sean correctos |
| Sesión expirada | Cierra el navegador y vuelve a abrir |
| Cuenta bloqueada | Contacta al administrador |

### 7.2 No veo datos en los dashboards

| Posible causa | Solución |
|---------------|----------|
| No hay datos cargados | Primero debes importar archivos (Cocina) o registrar atenciones (Médico) |
| Filtro incorrecto | Revisa que la fecha/mes seleccionado tenga datos |
| Permisos insuficientes | Contacta al administrador |

### 7.3 Error al subir un archivo de Cocina

| Error | Solución |
|-------|----------|
| "Archivo muy grande" | El máximo es 10 MB. Reduce el tamaño o divide en varios archivos |
| "Formato no soportado" | Usa archivos .xlsx, .xls o .csv |
| "No se encontraron encabezados" | Asegúrate de que el Excel tenga filas con "Fecha" y "Cantidad" |
| Errores por fila | Revisa que las fechas sean válidas y los productos tengan nombre |

### 7.4 Error al guardar una atención médica

| Error | Solución |
|-------|----------|
| "La causa es obligatoria" | Selecciona una causa de la lista |
| "Cantidad debe ser mayor a 0" | Verifica las cantidades de medicación |
| "La habitación es obligatoria" | Si el paciente es huésped, debe tener habitación |

### 7.5 El inventario no se actualiza

| Posible causa | Solución |
|---------------|----------|
| Movimiento no registrado | Revisa que el movimiento se haya guardado correctamente |
| Producto desactivado | Solo los productos activos aparecen en los listados |

---

## 8. Preguntas frecuentes

### ¿Puedo editar una atención después de guardarla?
Sí. Busca la atención en la lista y haz clic en **Editar**. Podrás modificar todos los campos.

### ¿Qué pasa si elimino una atención?
Se elimina el registro de atención y también se revierten los descuentos de inventario que se hicieron automáticamente.

### ¿Los productos de inventario y los medicamentos son lo mismo?
Están sincronizados. Cuando creas un producto de tipo "medicina" en el inventario, automáticamente se crea o vincula con un registro en el catálogo de medicamentos.

### ¿Puedo tener un producto con diferentes nombres?
Sí. El sistema permite crear **alias** para productos (a través de `MedicoProductoAlias`), lo que facilita la búsqueda por nombres alternativos.

### ¿Cómo sé qué medicamentos necesitan reposición?
El Dashboard Médico muestra una sección de **Alertas de stock** con los productos cuyo saldo es igual o menor a 2 unidades. También en el Inventario verás el contador de "Stock bajo".

### ¿Los reportes se pueden personalizar?
Los reportes se descargan en Excel (.xlsx), por lo que puedes editarlos y darles formato después de descargarlos.

### ¿El sistema funciona en dispositivos móviles?
Sí. El panel es responsivo y se adapta a pantallas de diferentes tamaños, aunque está optimizado para uso en computadoras.

### ¿Cada cuánto debo subir datos de cocina?
Depende del flujo de trabajo del departamento. Puedes subir un archivo por día o acumular varios y subirlos juntos. El sistema maneja deduplicación automática.

---

## Apéndice: Glosario

| Término | Significado |
|---------|-------------|
| **Parte diario** | Registro de atención médica individual |
| **Kardex** | Sistema de control de inventario con saldos mensuales |
| **Certificado médico** | Documento que justifica la ausencia del colaborador |
| **Tipo de salida** | Cómo terminó la atención (alta, reposo, etc.) |
| **Insumo** | Producto no farmacológico (gasas, vendas, etc.) |
| **Stock mínimo** | Cantidad a partir de la cual se considera que un producto necesita reposición |
| **Huésped** | Paciente que es huésped del hotel (no colaborador) |
| **Colaborador** | Empleado del hotel |
| **Examen ocupacional** | Examen médico periódico (espirometría, audiometría, etc.) |
| **Deduplicación** | Mecanismo que evita registrar la misma información dos veces |

---

> **¿Necesitas ayuda adicional?** Contacta al administrador del sistema o al equipo de soporte técnico.
>
> *Documentación generada en julio 2026 para el Sistema de Gestión Hotelera Wyndham Manta.*
