
# UNIVERSIDAD PRIVADA DE TACNA
## FACULTAD DE INGENIERÍA
### Escuela Profesional de Ingeniería de Sistemas

<br>

# **PROYECTO**
# **Convertidor Automático de Markdown a Video Interactivo “Markdown2Video”**

<br>

**Curso:** Calidad y Pruebas de Software

**Docente:** Ing. Patrick Jose Cuadros Quiroga

**Integrantes:**
*   Calizaya Ladera, Andy Michael (2022074258)
*   Camac Melendez, Cesar Nikolas (2022074262)
*   Fernandez Villanueva, Daleska Nicolle (2021070308)

<br>
<br>

**Tacna – Perú**
**2025**

<br>
<hr>
<br>

# Convertidor Automático de Markdown a Video Interactivo - Markdown2Video
## Informe de Factibilidad

### Versión 1.0

<br>
<br>

## CONTROL DE VERSIONES
| Versión | Hecha por | Revisada por | Aprobada por | Fecha | Motivo |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1.0 | MPV | ELV | ARV | 10/10/2020 | Versión Original |

## ÍNDICE GENERAL

1.  Descripción del Proyecto
2.  Riesgos
3.  Análisis de la Situación actual
4.  Estudio de Factibilidad
    4.1. Factibilidad Técnica
    4.2. Factibilidad económica
    4.3. Factibilidad Operativa
    4.4. Factibilidad Legal
    4.5. Factibilidad Social
    4.6. Factibilidad Ambiental
5.  Análisis Financiero
6.  Conclusiones

<br>
<hr>
<br>

# Informe de Factibilidad

## 1. Descripción del Proyecto
**Nombre del proyecto**
CONVERTIDOR AUTOMÁTICO DE MARKDOWN A VIDEO INTERACTIVO

**Duración del proyecto**
El proyecto inicia el 31 de marzo del 2025 y culmina el 18 de junio del año 2025, teniendo una duración de 2 meses con 19 días.

**Descripción**
El proyecto "Markdown2Video" es una herramienta que permite la conversión automática de documentos en formato Markdown a videos interactivos. Su propósito es facilitar la generación de contenido audiovisual sin necesidad de conocimientos avanzados en edición de video. A través del uso de tecnologías como FFmpeg, Text-To-Speech (TTS) y animaciones predefinidas, esta aplicación transformará documentos en videos con estructura visual atractiva, incluyendo transiciones, efectos, narración en voz en off y personalización de estilos.
El software estará disponible como una aplicación de escritorio con una interfaz gráfica intuitiva, así como una versión de línea de comandos para usuarios avanzados. Será compatible con Windows, macOS y Linux, ofreciendo opciones de exportación en formatos populares como MP4. Además, permitirá la integración con plataformas de distribución de videos como YouTube, facilitando la publicación de contenido generado automáticamente.

### 1.4 Objetivos
#### 1.4.1 Objetivo general
Desarrollar una herramienta que convierta documentos escritos en Markdown en videos interactivos, manteniendo la estructura del contenido y proporcionando opciones de personalización como animaciones, transiciones y narración por síntesis de voz, con el fin de facilitar la creación de contenido audiovisual de manera accesible y eficiente.

#### 1.4.2 Objetivos Específicos
*   Desarrollar un motor de conversión que interprete archivos Markdown y los transforme en un guión estructurado para la generación de video.
*   Implementar una interfaz gráfica intuitiva que permita a los usuarios cargar archivos Markdown, personalizar el estilo del video y exportar en formatos populares.
*   Optimizar la conversión y exportación de videos asegurando compatibilidad con múltiples plataformas y estándares de video.
*   Permitir la personalización del contenido audiovisual, incluyendo diseño, tipografía, colores, efectos de transición y música de fondo.
*   Facilitar la integración con plataformas de video, ofreciendo la opción de subir automáticamente los videos generados a servicios como YouTube.

## 2. Riesgos
*   **Dificultades en la conversión de contenido**, debido a algunas estructuras avanzadas de Markdown podrían no ser interpretadas correctamente en video, lo que podría generar errores en la conversión.
*   **Limitaciones en la síntesis de voz (TTS)**, ya que la calidad y naturalidad de la narración generada automáticamente puede no ser óptima, afectando la experiencia del usuario.
*   **Requerimientos computacionales elevados**, porque el procesamiento de video y audio puede demandar un alto consumo de recursos, dificultando su ejecución en equipos con hardware limitado.
*   **Compatibilidad con diferentes sistemas operativos**, porque debemos asegurar que la aplicación funcione correctamente en Windows, macOS y Linux puede presentar desafíos técnicos.
*   **Aceptación del mercado**, debido a que existe la posibilidad de que los usuarios prefieran herramientas de edición de video tradicionales, lo que afectaría la adopción del software.
*   **Fallos en la automatización**, haciendo referencia a los errores en la integración con FFmpeg o en la generación de transiciones y efectos podrían afectar la calidad final del vídeo.

## 3. Análisis de la Situación actual
### Planteamiento del problema
Actualmente, la creación de contenido audiovisual requiere conocimientos técnicos en edición de video y el uso de herramientas avanzadas como Adobe Premiere Pro, Final Cut Pro o DaVinci Resolve. Estas herramientas ofrecen un alto grado de personalización, pero presentan una curva de aprendizaje elevada y demandan un tiempo considerable para producir videos atractivos.
Por otro lado, Markdown es un formato ampliamente utilizado para estructurar información en blogs, documentación técnica y presentaciones. Sin embargo, no existe una solución accesible que permita transformar estos documentos en videos de manera automatizada y sin intervención manual.

La falta de una herramienta que convierta Markdown en videos limita a:
*   Docentes y capacitadores, que necesitan generar contenido educativo en vídeo sin complicaciones.
*   Creadores de contenido digital, que buscan una forma rápida de transformar su texto en videos para plataformas como YouTube.
*   Empresas y equipos de documentación, que desean convertir documentación técnica en material audiovisual sin necesidad de aprender edición de video.

Este proyecto abordará esta necesidad, proporcionando una solución rápida, automatizada y accesible para transformar documentos Markdown en contenido audiovisual atractivo.

### Consideraciones de hardware y software
Para el desarrollo del sistema se hará uso de la siguiente tecnología:

#### Hardware
| Hardware | Detalle |
| :--- | :--- |
| Servidores | 1 servidor con Windows Server - Elastika |
| Estaciones de trabajo | 3 computadoras para el equipo de desarrollo. |
| Red y Conectividad | Acceso a internet de alta velocidad. |

#### Software
| Software | Detalle |
| :--- | :--- |
| Sistema Operativo | Windows 10 para estaciones de trabajo |
| Base de Datos | MySQL 8 para gestionar los datos |
| Control de Versiones | Git (GitHub) |
| Navegadores Compatibles | Google Chrome |

#### Tecnologías de desarrollo
| Tecnología | Detalle |
| :--- | :--- |
| Lenguaje de Programación | PHP versión 8 |
| Backend | Desarrollo utilizando PHP versión 8 |
| Frontend | HTML5, CSS3, JavaScript |
| Plataforma de Desarrollo | IDEs como Visual Studio Code |

## 4. Estudio de Factibilidad
Este estudio busca evaluar la viabilidad del desarrollo e implementación del convertidor Markdown2Video, analizando aspectos técnicos, económicos, operativos, legales, sociales y ambientales.

### 4.1 Factibilidad Técnica
*   **Tecnología Disponible**: Las tecnologías necesarias para el desarrollo del sistema están ampliamente disponibles: Lenguajes y herramientas estándar: PHP 8, MySQL 8, HTML5, CSS3 y JavaScript son tecnologías consolidadas, con abundante documentación y soporte comunitario. Control de versiones: Git y GitHub son herramientas robustas para el trabajo colaborativo y el seguimiento de cambios.
*   **Experiencia del equipo**: Se asume que el equipo de desarrollo (estudiantes avanzados o egresados de Ingeniería de Sistemas) tiene experiencia en el stack de desarrollo web.
*   **Infraestructura Existente**: El sistema puede ser instalado y probado inicialmente en un servidor con Windows Server (Elastika), lo cual permite simular un entorno productivo. Las 3 estaciones de trabajo con Windows 10 y acceso a internet de alta velocidad son adecuadas para el desarrollo colaborativo y pruebas.
*   **Escalabilidad**: El proyecto puede iniciar como una aplicación web que genere videos a partir de texto en Markdown y, en el futuro, extenderse con funcionalidades avanzadas como: Exportación a diferentes resoluciones o estilos de video, compatibilidad con móviles.
*   **Integración**: También es posible añadir autenticación con cuentas institucionales.

### 4.2 Factibilidad Económica
Este apartado evalúa los costos asociados con el desarrollo del sistema.

#### Costos Generales
| Artículo | Cantidad | Precio Unitario | Precio Total |
| :--- | :--- | :--- | :--- |
| Computadora | 3 | S/ 1200 | S/ 3600 |
| **Total costos generales** | | | **S/ 3600** |

#### Costos operativos durante el desarrollo
| Descripción | Duración | Costo Mensual | Precio Total |
| :--- | :--- | :--- | :--- |
| Luz | 3 meses | S/ 40 | S/ 120 |
| Internet | 3 meses | S/ 40 | S/ 120 |
| **Total costos operativos** | | | **S/ 240** |

#### Costos del ambiente
| Descripción | Costo Mensual | Precio Total |
| :--- | :--- | :--- |
| Host del Servidor | S/ 40 | S/ 120 |
| Dominio | S/ 30 | S/ 90 |
| **Total costos ambientales** | | **S/ 210** |

#### Costo del personal
| Descripción | Cantidad | Duración | Sueldo Mensual | Precio Total |
| :--- | :--- | :--- | :--- | :--- |
| Desarrollador de UI | 1 | 40 horas semanal | S/ 1200 | S/ 3600 |
| Desarrollador | 1 | 40 horas semanal | S/ 1200 | S/ 3600 |
| Ingeniero de pruebas | 1 | 40 horas semanal | S/ 1200 | S/ 3600 |
| **Total costos de personal** | | | | **S/ 10800** |

#### Costos totales del desarrollo del sistema
| Concepto | Costo Total |
| :--- | :--- |
| Costos generales | S/. 3,600 |
| Costos operativos (3 meses)| S/. 240 |
| Costos del ambiente | S/. 210 |
| Costos de personal (3 meses)| S/. 10,800 |
| **Total** | **S/. 14,850** |

### 4.3 Factibilidad Operativa
El sistema Markdown2Video tiene un alto grado de factibilidad operativa debido a su enfoque en la automatización y facilidad de uso.
*   **Beneficios esperados:**
    *   Automatización de la creación de videos sin necesidad de conocimientos en edición.
    *   Interfaz intuitiva y accesible para todo tipo de usuarios.
    *   Reducción del tiempo y costos en la producción de videos educativos y empresariales.
    *   Generación de contenido en múltiples formatos con compatibilidad para diferentes plataformas.
*   **Lista de interesados:**
    *   Docentes y capacitadores.
    *   Creadores de contenido digital.
    *   Empresas y emprendedores.
    *   Equipos de documentación técnica.

### 4.4 Factibilidad Legal
El sistema Markdown2Video deberá cumplir con las siguientes regulaciones:
*   **Protección de Datos Personales** para asegurar la privacidad de los usuarios mediante políticas de seguridad de datos.
*   **Derechos de Autor**, de esta manera verificamos el uso de contenido libre de derechos en voces sintetizadas, imágenes y música de fondo.
*   **Regulaciones de Software Libre** para cumplir con las licencias de herramientas utilizadas como FFmpeg y motores de TTS.

### 4.5 Factibilidad Social
El impacto social del sistema será positivo, ya que facilitará la creación de contenido educativo y profesional sin barreras tecnológicas.
Aspectos a considerar:
*   **Inclusión y accesibilidad**, que permite a cualquier usuario generar contenido sin necesidad de experiencia en edición de video.
*   **Democratización de la educación**, lo cual fomenta la difusión de conocimiento mediante videos de calidad generados automáticamente.
*   **Posible rechazo de editores de video profesionales**, ello podría generar resistencia en comunidades de editores tradicionales.

### 4.6 Factibilidad Ambiental
El impacto ambiental del proyecto es mínimo, pero se considerarán las siguientes acciones:
*   Uso eficiente de recursos computacionales para reducir el consumo energético.
*   Opciones de procesamiento en la nube para optimizar la utilización de hardware.
*   Digitalización de documentos para evitar la impresión innecesaria de material físico.

## 5. Análisis Financiero
El plan financiero se ocupa del análisis de ingresos y gastos asociados a cada proyecto, desde el punto de vista del instante temporal en que se producen. Su misión fundamental es detectar situaciones financieramente inadecuadas.
Se tiene que estimar financieramente el resultado del proyecto.

### 5.1 Justificación de la Inversión
#### Beneficios Tangibles:
*   Reducción del tiempo de creación de contenido educativo en un 50%, al automatizar la generación de videos a partir de textos en formato Markdown.
*   Disminución del uso de software de edición compleja en un 40%, gracias a la automatización del proceso audiovisual.
*   Ahorro en licencias de software de edición profesional, al usar herramientas open source o integraciones propias.
*   Optimización de recursos del área de tecnología educativa, al centralizar la producción de contenido en un solo sistema web.

#### Beneficios Intangibles:
*   Mejora de la experiencia docente y estudiantil, al permitir la creación rápida de vídeos explicativos.
*   Modernización de los métodos de enseñanza y aprendizaje mediante recursos multimedia accesibles.
*   Fomento de la innovación educativa dentro de la institución.
*   Mejora de la accesibilidad al contenido para estudiantes con diferentes estilos de aprendizaje.

### 5.1.2 Criterios de Inversión
| Categoría | Detalle del Beneficio | Beneficio Estimado (S/.) |
| :--- | :--- | :--- |
| **A) Ahorro en Recursos Físicos** | | |
| | Reducción en impresiones de trabajos (al usar videos en lugar de documentos impresos) | 180 |
| | Ahorro en materiales físicos (CDs, carpetas, presentaciones físicas) | 90 |
| | Digitalización de entregables académicos (sin uso de materiales físicos) | 130 |
| **Subtotal A** | | **S/. 400** |
| **B) Ahorro en Tiempo Estudiantil** | | |
| | Automatización de creación de videos desde Markdown | 320 |
| | Reducción del tiempo dedicado a edición manual de video | 260 |
| | Generación automática de títulos, transiciones y subtítulos | 200 |
| **Subtotal B** | | **S/. 780** |
| **C) Eficiencia Académica** | | |
| | Mayor enfoque en contenidos y comprensión (no en edición técnica) | 230 |
| | Mejora en la calidad de entregables visuales (presentaciones más claras y ordenadas) | 210 |
| **Subtotal C** | | **S/. 440** |
| **D) Retención Estudiantil y Motivación** | | |
| | Incremento del interés en las actividades académicas mediante uso de tecnología moderna | 260 |
| | Reducción del estrés por carga técnica (edición, entrega) | 240 |
| **Subtotal D** | | **S/. 500** |
| **E) Ahorro por Automatización** | | |
| | Eliminación de tareas repetitivas (como agregar títulos, subtítulos, transiciones) | 310 |
| | Reducción de errores en la edición manual de videos | 250 |
| **Subtotal E** | | **S/. 560** |
| **TOTAL BENEFICIOS MENSUALES ESTIMADOS** | | **S/. 2,680** |

#### 5.1.2.1 Relación Beneficio/Costo (B/C)
La relación Beneficio/Costo (B/C) del proyecto es de **2.09**. Este ratio compara el valor presente de los beneficios con el valor presente de los costos. Un B/C mayor que 1, como en este caso, indica que los beneficios superan ampliamente los costos, lo que hace que el proyecto sea económicamente viable y atractivo para su ejecución.

| Concepto | Valor (S/.) |
| :--- | :--- |
| Beneficios (12 meses) | S/. 2,680 × 12 = S/. 32,160 |
| Costos reales | S/.14,777.23 |
| **B/C** | **2.09** |

#### 5.1.2.2 Valor Actual Neto (VAN)
El VAN es de **S/.16,170.95**. Esto significa que después de descontar los flujos de efectivo futuros a una tasa de descuento del 10%, el valor presente neto de los ingresos esperados del proyecto es positivo. Un VAN positivo indica que el proyecto generará más valor del que cuesta, por lo tanto, es financieramente viable y debería ser considerado para su implementación.

VAN = Valor Actual de los Beneficios − Valor Actual de los Costos
VAN = 30,948.18 − 14,777.23
VAN = S/.16,170.95

#### 5.1.2.3 Tasa Interna de Retorno (TIR)
La Tasa Interna de Retorno (TIR) del proyecto es de **8.80% mensual**, lo cual supera significativamente la tasa de descuento utilizada del 10%. Esta tasa refleja una alta rentabilidad sobre la inversión realizada. Una TIR mayor que la tasa de oportunidad indica que el proyecto no solo es viable, sino que ofrece un retorno atractivo con respecto al riesgo asumido.

## 6. Conclusiones
Se concluye que el sistema markdown2video es técnicamente factible, ya que se basa en tecnologías ampliamente disponibles y bien comprendidas, como PHP 8, MySQL 8, HTML5, CSS3 y JavaScript. El equipo de desarrollo contará con estaciones de trabajo adecuadas y un servidor con Windows Server, dentro de una infraestructura ya existente en la universidad, lo que asegura viabilidad operativa sin necesidad de grandes inversiones iniciales en hardware adicional.

El sistema ha sido concebido específicamente para estudiantes universitarios, respondiendo a una necesidad concreta de automatizar la creación de videos académicos desde documentos estructurados en Markdown. Esta funcionalidad permite reducir significativamente el tiempo y esfuerzo invertido en tareas repetitivas como la edición de video, generación de títulos, subtítulos y transiciones. Por tanto, su factibilidad operativa también se ve respaldada por su alineación directa con las necesidades del usuario final.

Desde el punto de vista económico, el análisis financiero demuestra una viabilidad clara y alentadora. Con beneficios mensuales estimados en S/. 2,680 y un costo de desarrollo total de S/. 14,850, el proyecto presenta un Valor Actual Neto (VAN) de S/. 14,009.72, una Tasa Interna de Retorno (TIR) anual del 8.80% y una Relación Beneficio/Costo (B/C) de 2.09. Estos indicadores financieros evidencian una alta rentabilidad y un retorno significativo sobre la inversión, incluso considerando un horizonte de evaluación conservador de 12 meses.

Finalmente, la implementación de markdown2video representa una oportunidad estratégica para mejorar la productividad académica de los estudiantes, reducir costos operativos ligados a la edición manual de contenidos audiovisuales y fomentar el uso de herramientas tecnológicas accesibles y sostenibles dentro del entorno educativo. El proyecto no solo es viable desde el punto de vista técnico y económico, sino también altamente beneficioso para su público objetivo.