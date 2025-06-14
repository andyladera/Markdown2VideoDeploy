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

## CONTROL DE VERSIONES
| Versión | Hecha por | Revisada por | Aprobada por | Fecha | Motivo |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1.0 | DFV | | | 21/03/2025 | Versión Original |

<br>
<br>

# Convertidor Automático de Markdown a Video Interactivo - Markdown2Video
## Informe de Factibilidad

### Versión 1.0

<br>
<br>

## CONTROL DE VERSIONES
| Versión | Hecha por | Revisada por | Aprobada por | Fecha | Motivo |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1.0 | DFV | | | 21/03/2025 | Versión Original |

## ÍNDICE

1.  Introducción
    1.1. Propósito
    1.2. Alcance
    1.3. Definiciones, Siglas y Abreviaturas
    1.4. Referencias
    1.5. Visión General
2.  Posicionamiento
    2.1. Oportunidad de Negocio
    2.2. Definición del Problema
3.  Descripcion de interesados y usuarios
    3.1. Resumen de los interesados
    3.2. Resumen de los usuario
    3.3. Entorno de usuario
    3.4. Perfiles de los interesados
    3.5. Perfiles de los usuarios
    3.6. Necesidad de los interesados y usuarios
4.  Vista General del Producto
    4.1. Perspectiva del Producto
    4.2. Resumen de Capacidades
    4.3. Suposiciones y Dependencias
    4.4. Costos y Precios
    4.5. Licenciamiento e instalación
5.  Características del Producto
6.  Restricciones

<br>
<hr>
<br>

# 1. Introducción

## 1.1. Propósito
El propósito de este proyecto es desarrollar una herramienta que permita la conversión automática de presentaciones creadas en formato Markdown con Marp a archivos de vídeo. Actualmente, Marp facilita la creación de diapositivas mediante sintaxis Markdown, ofreciendo una solución eficiente para la generación de presentaciones en entornos de desarrollo. Sin embargo, no cuenta con una funcionalidad nativa para exportar dichas presentaciones en formato de video, lo que limita su aplicación en contextos donde se requiere contenido audiovisual sin intervención manual.

Este proyecto busca innovar en este aspecto al proporcionar una plataforma que no solo permite la escritura y previsualización en tiempo real de presentaciones en código Marp, sino que también integre un mecanismo de conversión automática a video. De esta manera, se optimiza el proceso de generación de contenido audiovisual para presentaciones académicas, cursos en línea, conferencias y otros escenarios donde la distribución de información en video es esencial.

La implementación de esta herramienta reducirá la necesidad de utilizar software de edición de video externo, agilizando el flujo de trabajo de los usuarios y permitiendo una mayor accesibilidad y automatización en la producción de presentaciones dinámicas.

## 1.2. Alcance
El proyecto abarcará el desarrollo de una aplicación que incluya las siguientes funcionalidades:
*   **Editor de Código Marp:** Permite a los usuarios escribir presentaciones en lenguaje Markdown con soporte para Marp.
*   **Vista Previa en Tiempo Real:** Mostrar en una interfaz visual cómo se verá la presentación en diapositivas mientras se edita el código.
*   **Conversión a Video:** Implementar un sistema que transforme automáticamente las diapositivas en un archivo de video con transiciones predefinidas.
*   **Opciones de Personalización:** Incluir configuraciones para ajustar la resolución, duración de las diapositivas y otros parámetros del video.
*   **Compatibilidad Multiplataforma:** Desarrollar la herramienta para que sea accesible en diferentes sistemas operativos.

## 1.3. Definiciones, Siglas y Abreviaturas
| Sigla/Abreviatura | Definición |
| :--- | :--- |
| **Markdown** | Lenguaje de marcado ligero para la escritura de texto estructurado. |
| **MP4** | Formato de video ampliamente compatible y de alta compresión. |
| **FFmpeg** | Conjunto de herramientas para procesamiento de video y audio. |
| **TTS** | Texto a voz (Text-To-Speech), tecnología que convierte texto en audio hablado. |

## 1.4. Referencias
El convertidor de Markdown a video se posicionará como una herramienta innovadora que automatiza la generación de contenido audiovisual sin requerir conocimientos técnicos avanzados. A diferencia de editores de video tradicionales, este sistema permitirá la conversión rápida de texto a video, manteniendo la estructura y semántica del contenido original.

## 1.5. Visión General
En la actualidad, la creación de videos educativos, promocionales y de divulgación requiere herramientas de edición complejas y tiempo significativo para su producción. Este convertidor abordará esta necesidad ofreciendo una solución que automatiza el proceso y permite a cualquier usuario generar contenido visual de calidad con mínima intervención manual.

# 2. Posicionamiento

## 2.1. Oportunidad de Negocio
El sistema se posicionará como una herramienta innovadora para la automatización de la creación de videos a partir de presentaciones Markdown. Su principal diferenciador será la conservación fiel de animaciones y diseños de Marp, sin requerir edición manual posterior.

## 2.2. Definición del Problema
En la actualidad, la creación de contenido audiovisual, especialmente en forma de videos educativos, promocionales o de divulgación, representa un desafío significativo para muchas personas y organizaciones. La producción de videos de calidad requiere conocimientos en edición, herramientas especializadas y tiempo considerable para desarrollar contenido atractivo.

Las herramientas tradicionales de edición de video, como Adobe Premiere, Final Cut Pro o DaVinci Resolve, ofrecen un alto grado de personalización, pero requieren una curva de aprendizaje elevada y demanda de recursos computacionales considerables. Esto limita su accesibilidad a personas que no tienen experiencia en edición de video o que necesitan generar contenido de forma rápida y eficiente sin dedicar largas horas a la edición manual.

Por otro lado, Markdown se ha convertido en un estándar ampliamente utilizado para la escritura estructurada de contenido en múltiples ámbitos, como documentación técnica, blogs y presentaciones. Sin embargo, actualmente no existe una solución accesible y automatizada que permita convertir estos documentos en videos interactivos sin necesidad de intervención manual.

Este vacío en el mercado crea una barrera para creadores de contenido, docentes, empresas y profesionales que buscan transformar documentos en videos sin enfrentarse a procesos complicados de edición. En muchos casos, estas personas terminan utilizando herramientas de presentación como PowerPoint o Google Slides con grabaciones de pantalla, lo que puede ser un proceso tedioso y con limitaciones en cuanto a personalización y calidad.

# 3. Descripcion de interesados y usuarios

## 3.1. Resumen de los interesados
Los interesados en el sistema incluyen diversas personas y entidades que pueden beneficiarse de la conversión automatizada de Markdown a video. Entre ellos se encuentran:
*   **Docentes y capacitadores:** Necesitan transformar materiales educativos en videos accesibles y atractivos para sus estudiantes.
*   **Creadores de contenido digital:** Bloggers, escritores y profesionales del marketing que buscan generar contenido audiovisual a partir de textos estructurados.
*   **Empresas y emprendedores:** Requieren herramientas para la creación rápida de vídeos explicativos y promocionales.
*   **Desarrolladores de software y equipos de documentación:** Utilizan Markdown para escribir documentación técnica y pueden beneficiarse de su conversión a vídeo.
*   **Estudiantes y autodidactas:** Buscan nuevas formas de presentar información y facilitar el aprendizaje mediante contenido audiovisual.

## 3.2. Resumen de los usuarios
Los usuarios del sistema son aquellas personas que utilizarán directamente la herramienta para convertir documentos Markdown en videos. Los principales perfiles incluyen:
*   **Usuarios sin conocimientos de edición de video:** Personas que necesitan generar videos sin conocimientos previos en herramientas avanzadas de edición.
*   **Usuarios con experiencia en Markdown:** Desarrolladores y redactores técnicos que desean convertir su documentación en presentaciones dinámicas.
*   **Usuarios avanzados en edición de video:** Profesionales que buscan una solución rápida para generar material base que puedan mejorar en otros programas de edición.

## 3.3. Entorno de usuario
### Plataformas Compatibles:
*   **Sistema operativo:** Windows, macOS y Linux.

### Requisitos técnicos:
*   Procesador de rendimiento medio-alto.
*   Memoria RAM suficiente para procesamiento de video.
*   Instalación de FFmpeg para procesamiento de video y audio.

### Interfaz de Usuario
*   Aplicación de escritorio con interfaz gráfica intuitiva.
*   Línea de comandos para usuarios avanzados que deseen automatizar la conversión de múltiples archivos.
*   Integración con herramientas de terceros para facilitar la distribución de videos en plataformas como YouTube.

## 3.4. Perfiles de los interesados
*   **Creadores de Contenido Digital:** Bloggers, escritores técnicos y profesionales del marketing que crean contenido escrito (artículos, tutoriales, blogs, guías) en Markdown y buscan transformarlo en contenido audiovisual.
*   **Empresas y Emprendedores:** Empresas que buscan herramientas para la creación rápida de vídeos explicativos o promocionales, y emprendedores que necesitan presentar sus productos o servicios de manera efectiva.
*   **Desarrolladores de Software y Equipos de Documentación:** Equipos que crean documentación técnica en Markdown, como manuales, guías de usuario, o documentación de software.
*   **Estudiantes y Autodidactas:** Estudiantes de diversas áreas que están aprendiendo por su cuenta y desean hacer presentaciones audiovisuales de los temas que están estudiando, o autodidactas que buscan material más interactivo.
*   **Docentes y Capacitadores:** Profesores, instructores y formadores que crean materiales educativos para sus estudiantes, como guías, tutoriales y lecciones en formato Markdown. Están interesados en una forma rápida y eficiente de convertir sus materiales escritos en contenido visual interactivo.

## 3.5. Perfiles de los usuarios
*   **Usuarios sin Conocimientos de Edición de Video:** Personas que no tienen experiencia en la edición de videos pero necesitan generar contenido audiovisual, como docentes o empresarios pequeños.
*   **Usuarios con Experiencia en Markdown:** Desarrolladores, técnicos, o escritores de documentación que ya están familiarizados con Markdown y necesitan una herramienta que facilite la conversión de su contenido a un formato más visual.
*   **Usuarios Avanzados en Edición de Video:** Profesionales que ya tienen experiencia con edición de video y desean generar rápidamente contenido base (videos iniciales) que luego puedan perfeccionar en herramientas especializadas como Adobe Premiere o Final Cut Pro.

## 3.6. Necesidad de los interesados y usuarios
*   **Docentes y Capacitadores:** Los docentes necesitan herramientas simples y eficaces para transformar materiales escritos en videos educativos sin requerir experiencia técnica avanzada en edición de video.
*   **Creadores de Contenido Digital:** Los creadores de contenido desean crear material audiovisual de manera rápida y efectiva, utilizando sus escritos en Markdown sin tener que invertir tiempo en edición manual.
*   **Empresas y Emprendedores:** Las empresas requieren una forma rápida de crear videos explicativos y promocionales para sus productos y servicios.
*   **Desarrolladores de Software y Equipos de Documentación:** Necesitan una forma de convertir rápidamente la documentación técnica escrita en Markdown en videos tutoriales o guías visuales que puedan ser utilizados en capacitaciones o compartidos con clientes.

# 4. Vista General del Producto

## 4.1. Perspectiva del Producto
Este producto se posiciona como una herramienta innovadora dentro del mercado de creación de contenido audiovisual, particularmente para personas y organizaciones que necesitan convertir documentos escritos en videos sin conocimientos avanzados de edición. Al automatizar el proceso de creación de videos, el convertidor permite a los usuarios crear presentaciones de alta calidad a partir de su contenido Markdown de forma sencilla y accesible. Además, se integra fácilmente con plataformas de distribución como YouTube, facilitando la difusión del contenido generado. La solución se diferencia de las herramientas tradicionales de edición de video por su enfoque simplificado, lo que hace que la creación de videos sea accesible a una audiencia más amplia.

## 4.2. Resumen de Capacidades
*   **Conversión Automática de Markdown a Video:** El sistema convierte documentos Markdown en videos sin intervención manual, manteniendo la estructura y contenido original.
*   **Texto a Voz (TTS):** Integra tecnología de texto a voz (TTS) para incluir narración automática, lo que permite la creación de videos educativos y tutoriales sin necesidad de grabar voces.
*   **Soporte de Archivos MP4:** Los videos se generan en formato MP4, ampliamente compatible con plataformas de distribución como YouTube, Vimeo y redes sociales.
*   **Elementos Interactivos:** Los usuarios pueden incluir botones de navegación y enlaces clickables dentro del video, mejorando la interactividad y experiencia del espectador.
*   **Personalización de Estilos:** Los usuarios pueden personalizar elementos como tipografía, colores, y efectos visuales para adaptarse a sus necesidades o branding.
*   **Interfaz Intuitiva:** La herramienta cuenta con una interfaz gráfica de usuario (GUI) amigable, además de una línea de comandos para usuarios avanzados que deseen automatizar procesos.

## 4.3. Suposiciones y Dependencias
### Suposiciones:
*   Los usuarios cuentan con archivos Markdown bien estructurados como base para la conversión.
*   Los usuarios tienen acceso a una conexión a Internet para descargar la aplicación y actualizaciones del sistema.
*   Se supone que los usuarios tienen equipos que cumplen con los requisitos mínimos de hardware para ejecutar el software (procesadores medios-altos y suficiente memoria RAM).
*   La herramienta depende de FFmpeg para la codificación de video y audio, por lo que debe ser instalada y configurada adecuadamente.
*   Los usuarios necesitan acceso a servicios de texto a voz (TTS) si desean incluir voz en los videos generados.

### Dependencias:
*   **FFmpeg:** Necesario para el procesamiento de video y audio.
*   **Sistemas Operativos:** La herramienta dependerá de versiones compatibles de Windows, macOS y Linux.
*   **Bibliotecas de TTS:** La conversión de texto a voz requiere integración con bibliotecas de TTS, como Google TTS, Amazon Polly, o similares.
*   **Conexión a Internet:** La instalación del producto y las actualizaciones periódicas pueden requerir acceso a Internet.

## 4.4. Costos y Precios
*   **Versión Básica:** Gratis con funcionalidades limitadas (por ejemplo, exportación de vídeos a resolución estándar, opciones limitadas de TTS y personalización).
*   **Versión Premium:** $49.99 - $99.99 por usuario, con acceso a todas las funcionalidades avanzadas (exportación en 4K, personalización completa, acceso a bibliotecas TTS premium).

## 4.5. Licenciamiento e instalación
### Licenciamiento
El software estará disponible bajo un modelo de suscripción basado en el uso de la aplicación web, lo que permitirá a los usuarios acceder a las funcionalidades del sistema directamente a través de un navegador. El sistema de licencias estará basado en los siguientes planes:

*   **Licencia Freemium:**
    *   Acceso a una versión básica de la aplicación con funcionalidades limitadas.
    *   Los usuarios pueden realizar conversiones limitadas por mes o con acceso restringido a ciertas características avanzadas como personalización de animaciones, TTS, o exportación en resoluciones más altas.
    *   Esta licencia es ideal para usuarios ocasionales o para probar la herramienta.

*   **Licencia Individual:**
    *   **Precio:** $9.99/mes o $99.99/año.
    *   Permite acceso completo a todas las funcionalidades estándar de la aplicación, incluyendo conversiones ilimitadas de Markdown a video, personalización avanzada, exportación en MP4 de alta calidad y voz en off (TTS) en múltiples idiomas.
    *   Ideal para creadores de contenido y profesionales independientes.

### Instalación
Como aplicación web, no se requiere instalación de software tradicional en el dispositivo del usuario. En su lugar, el acceso a la herramienta se realizará a través de un navegador web. A continuación, se detallan los pasos de acceso y los requisitos.

# 5. Características del Producto
*   **Conversión Automática de Markdown a Video:** El producto convierte archivos Markdown en videos automáticamente, manteniendo la estructura original del contenido, como encabezados, listas, enlaces y texto.
*   **Soporte de Formatos de Video:** El producto permite la exportación de los videos generados en formato MP4, compatible con la mayoría de las plataformas de distribución y reproducción.
*   **Subida y Gestión de Archivos Markdown:** Los usuarios pueden cargar archivos Markdown (.md) directamente a la plataforma desde su computadora o desde servicios en la nube como Google Drive o Dropbox.
*   **Interfaz de Usuario Intuitiva:** Una interfaz fácil de usar, diseñada para usuarios sin experiencia en edición de video, que permite arrastrar y soltar elementos, personalizar configuraciones y previsualizar el video antes de la exportación.

# 6. Restricciones
*   **Limitaciones de Funcionalidades en la Versión Gratuita:** Los usuarios con la versión gratuita solo podrán realizar un número determinado de conversiones de archivos Markdown a video por mes (por ejemplo, 3 conversiones por mes).
*   **Dependencia de Conexión a Internet:** Al ser una aplicación basada en la web, el sistema requiere una conexión a Internet constante y estable para cargar archivos Markdown, procesar el video y realizar la exportación. No será posible usar la herramienta sin conexión.
*   **Capacidad de Almacenamiento Limitada en Planes Gratuitos:** Los usuarios de la versión gratuita tendrán un límite en la cantidad de videos almacenados en su cuenta en la nube (por ejemplo, hasta 5 videos almacenados simultáneamente).
*   **Limitaciones en la Personalización de Videos:** Los usuarios que utilicen la versión gratuita tendrán acceso solo a plantillas y configuraciones básicas para los videos (por ejemplo, animaciones simples y una selección limitada de estilos visuales).