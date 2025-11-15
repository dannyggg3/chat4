# Chat4 (ChatIAV) - Sistema de Chat en Vivo Profesional

## 📋 Descripción

Sistema completo de chat en vivo (Live Chat) para sitios web desarrollado con CodeIgniter 3 y AngularJS. Permite comunicación en tiempo real entre visitantes y agentes de soporte con panel de administración, mensajes enlatados, etiquetado de usuarios y notificaciones push (GCM).

## 🛠️ Stack Tecnológico

**Backend:**
- CodeIgniter 3 (PHP MVC)
- MySQL/MySQLi
- Autenticación con tokens

**Frontend:**
- AngularJS
- Angular-Bootstrap
- jQuery 1.8.0
- Bootstrap + Font Awesome

**Features:**
- Service Worker (notificaciones push)
- Google Cloud Messaging (GCM)
- WebSockets simulado

## 🏗️ Arquitectura

**Jerarquía de Controladores:**
```
CI_Controller
└── CP_Controller (Base)
    ├── CP_AdminController
    ├── CP_AgentController
    ├── CP_VisitorController
    └── CP_AppController
```

**Estructura:**
```
application/
├── controllers/  → 24 controladores (Admin, Agents, API, Desktop)
├── models/       → 15 modelos (Chat_message, Chat_session, etc.)
├── views/        → Layouts + módulo chat
├── core/         → Controladores base extendidos
├── libraries/    → Authentication, Media, Curl
└── config/       → Rutas, BD, constantes

assets/
├── cmodule/      → Módulo chat principal
├── cmodule-chat/ → Componente chat avanzado
└── angular-*/    → Componentes AngularJS
```

## ✨ Características

### 💬 Chat en Tiempo Real
- Widget embebible (iframe)
- Múltiples usuarios simultáneos
- Historial de conversaciones
- Estados online/offline

### 🎯 Panel de Agentes
- Gestión de solicitudes de chat
- Respuestas rápidas (mensajes enlatados)
- Dashboard con métricas
- Cambio de disponibilidad

### 👨‍💼 Administración
- Gestión de usuarios (admin/agentes)
- Etiquetas para categorizar
- Configuración (colores, logos)
- Historial completo
- Feedback de usuarios
- Solicitudes offline

### 📱 Visitantes
- Formulario de inicio chat
- Interface responsive
- Emojis/smilies
- Subida de archivos
- Notificaciones push

### 🔌 API REST
- Integración externa
- Tokens de acceso
- Endpoints documentados

## 🔧 Instalación

```bash
# 1. Clonar
git clone https://github.com/dannyggg3/chat4.git
cd chat4

# 2. Configurar BD
# Editar application/config/database.php

# 3. Importar SQL
# Ejecutar script de BD (14 tablas)

# 4. Configurar
# application/config/config.php

# 5. Servidor
# DocumentRoot: /ruta/chat4
```

## 💻 Uso

### Widget Embebido

```html
<!-- En tu sitio web -->
<script src="https://tudominio.com/assets/cmodule-chat/js/chatbox.js"></script>
<script>
  ChatIAV.init({
    domain: 'https://tudominio.com',
    token: 'TU_TOKEN_API'
  });
</script>
```

### Dashboard

Acceder a `/admin` con credenciales configuradas.

## 📊 Estadísticas

| Métrica | Valor |
|---------|-------|
| Controllers | 24 |
| Models | 15 |
| Tablas BD | 14 |
| Líneas código | ~10k+ |
| Framework | CodeIgniter 3 |

## 🔒 Seguridad

- ✅ Tokens de autenticación
- ✅ Validación de datos
- ✅ Sistema de permisos
- ✅ Sesiones seguras

## 🚀 Características Técnicas

- AJAX para actualización en tiempo real
- Service Worker para notificaciones
- GCM (Google Cloud Messaging)
- AngularJS para interfaz dinámica
- CodeIgniter para backend robusto

## 📄 Licencia

MIT - Proyecto parte del portafolio de dannyggg3

## 👤 Autor

**dannyggg3** - [@dannyggg3](https://github.com/dannyggg3)

---

⭐ Sistema profesional de soporte al cliente en tiempo real
