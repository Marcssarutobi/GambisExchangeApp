import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './components/router'
import 'preline/preline';

// Durée max d'inactivité = 30 minutes (en ms)
const MAX_IDLE_TIME = 30 * 60 * 1000; 
function updateLastActivity(){
    localStorage.setItem('lastActivity', Date.now())
}

// Suivre les événements de l’utilisateur
['click', 'mousemove', 'keydown', 'scroll'].forEach(event => {
    window.addEventListener(event, updateLastActivity);
});

// Vérifier l’inactivité toutes les minutes
setInterval(() => {
    const lastActivity = localStorage.getItem('lastActivity');
    const token = localStorage.getItem('token');
  
    if (token && lastActivity) {
      const now = Date.now();
      if (now - lastActivity > MAX_IDLE_TIME) {
        // Déconnexion
        localStorage.removeItem('token');
        localStorage.removeItem('lastActivity');
        window.location.href = '/login'; 
      }
    }
}, 60 * 1000);

// Init au démarrage
updateLastActivity();

const app = createApp(App);
app.use(router);
app.mount('#app');

// Initialize Preline UI with a delay to ensure DOM is ready
setTimeout(() => {
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }
}, 100);