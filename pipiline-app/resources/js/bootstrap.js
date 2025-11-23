import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Attach Authorization header from localStorage if available
function getStoredToken() {
	try {
		return localStorage.getItem('token') || localStorage.getItem('api_token') || localStorage.getItem('auth_token')
	} catch (e) {
		return null
	}
}

const initialToken = getStoredToken()
if (initialToken) {
	window.axios.defaults.headers.common['Authorization'] = `Bearer ${initialToken}`
}

// Keep axios header in sync with localStorage changes (other tabs)
window.addEventListener('storage', (e) => {
	if (e.key === 'token' || e.key === 'api_token' || e.key === 'auth_token') {
		if (e.newValue) window.axios.defaults.headers.common['Authorization'] = `Bearer ${e.newValue}`
		else delete window.axios.defaults.headers.common['Authorization']
	}
})

// Response interceptor: on 401, clear token and force login page
window.axios.interceptors.response.use(
	response => response,
	error => {
		const status = error?.response?.status
		if (status === 401) {
			try { localStorage.removeItem('token'); localStorage.removeItem('api_token'); localStorage.removeItem('auth_token') } catch (e) {}
			// redirect to login to re-authenticate
			try { window.location.href = '/login' } catch (e) {}
		}
		return Promise.reject(error)
	}
)
