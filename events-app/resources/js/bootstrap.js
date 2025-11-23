import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Attach Authorization header from localStorage for each request
axios.interceptors.request.use(function (config) {
	try {
		const token = localStorage.getItem('token')
		if (token) {
			config.headers = config.headers || {}
			config.headers['Authorization'] = `Bearer ${token}`
		}
	} catch (e) {
		// ignore
	}
	return config
}, function (error) {
	return Promise.reject(error)
})

// On 401 responses, clear token and redirect to login
axios.interceptors.response.use(function (response) {
	return response
}, function (error) {
	try {
		if (error && error.response && error.response.status === 401) {
			try { localStorage.removeItem('token'); localStorage.removeItem('user') } catch (e) {}
			// Force navigation to login (full reload ensures state is clean)
			window.location.href = '/login'
		}
	} catch (e) {}
	return Promise.reject(error)
})
