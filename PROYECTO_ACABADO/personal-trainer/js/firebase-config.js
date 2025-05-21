// Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyAHzlbNvClWUyIKGnClE7Bdnf2JYAJVVoE",
  authDomain: "fitlife-personal-trainer.firebaseapp.com",
  projectId: "fitlife-personal-trainer",
  storageBucket: "fitlife-personal-trainer.appspot.com",
  messagingSenderId: "1090699970152",
  appId: "1:1090699970152:web:94812d7c39453de41e1ddd",
  measurementId: "G-4VGLPVF9BE"
};


// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Auth providers
const googleProvider = new firebase.auth.GoogleAuthProvider();
const facebookProvider = new firebase.auth.FacebookAuthProvider();

// Add scopes for providers
googleProvider.addScope('profile');
googleProvider.addScope('email');
facebookProvider.addScope('email');
facebookProvider.addScope('public_profile');

// Configure language
firebase.auth().useDeviceLanguage();

// Handle auth state changes
firebase.auth().onAuthStateChanged((user) => {
    if (user) {
        console.log('Usuario autenticado:', user.email);
    } else {
        console.log('Usuario no autenticado');
    }
});

// Función para manejar errores de autenticación
function handleAuthError(error) {
    console.error('Error de autenticación:', error);
    let errorMessage = 'Ha ocurrido un error durante la autenticación.';
    
    switch (error.code) {
        case 'auth/account-exists-with-different-credential':
            errorMessage = 'Ya existe una cuenta con este email usando otro método de inicio de sesión.';
            break;
        case 'auth/popup-blocked':
            errorMessage = 'El popup de autenticación fue bloqueado. Por favor, permite popups para este sitio.';
            break;
        case 'auth/popup-closed-by-user':
            errorMessage = 'El proceso de autenticación fue cancelado.';
            break;
        case 'auth/unauthorized-domain':
            errorMessage = 'Este dominio no está autorizado para operaciones de OAuth.';
            break;
    }
    
    alert(errorMessage);
}

// Función para manejar el éxito de autenticación
function handleAuthSuccess(result, provider) {
    const user = result.user;
    const userData = {
        email: user.email,
        name: user.displayName,
        uid: user.uid,
        photoURL: user.photoURL,
        provider: provider
    };

    // Enviar datos al servidor
    fetch(`auth/${provider}-callback.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            throw new Error(data.message || 'Error en el servidor');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar el inicio de sesión: ' + error.message);
    });
}

// Función para iniciar sesión con Google
window.signInWithGoogle = function() {
    firebase.auth()
        .signInWithPopup(googleProvider)
        .then(result => handleAuthSuccess(result, 'google'))
        .catch(handleAuthError);
};

// Función para iniciar sesión con Facebook
window.signInWithFacebook = function() {
    firebase.auth()
        .signInWithPopup(facebookProvider)
        .then(result => handleAuthSuccess(result, 'facebook'))
        .catch(handleAuthError);
};
