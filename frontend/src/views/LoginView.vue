<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h1 class="logo-title">📚 MyBook</h1>
        <p class="logo-subtitle">Personal Book & Comic Vault</p>
      </div>

      <!-- Alert Pesan Error -->
      <div v-if="errorMessage" class="alert-error">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Alamat Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="admin@mybook.test"
            required
            class="form-control"
          />
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            required
            class="form-control"
          />
        </div>

        <button type="submit" :disabled="loading" class="btn-primary">
          <span v-if="loading">Memproses...</span>
          <span v-else>Masuk ke Sistem</span>
        </button>
      </form>

      <!-- Informasi Akun Default untuk Penguji / Dosen -->
      <div class="demo-info">
        <p class="demo-title">Kredensial Default:</p>
        <p>Email: <code>admin@mybook.test</code></p>
        <p>Password: <code>password123</code></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '../services/api';

const router = useRouter();
const email = ref('admin@mybook.test');
const password = ref('password123');
const loading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await apiClient.post('/login', {
      email: email.value,
      password: password.value,
    });

    if (response.data.status === 'success') {
      const { token, user } = response.data.data;
      localStorage.setItem('mybook_token', token);
      localStorage.setItem('mybook_user', JSON.stringify(user));
      router.push('/');
    }
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Kredensial tidak valid atau server belum aktif.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  min-height: 100vh;
  padding: 1.5rem;
  background-color: #f1f5f9;
}

.login-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 12px;
  padding: 2.5rem 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.25rem;
}

.logo-subtitle {
  font-size: 0.875rem;
  color: #64748b;
}

.alert-error {
  background-color: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
}

.form-control {
  width: 100%;
  padding: 0.7rem 0.85rem;
  background-color: #ffffff;
  color: #0f172a;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.btn-primary {
  width: 100%;
  padding: 0.75rem;
  background-color: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 0.5rem;
}

.btn-primary:hover:not(:disabled) {
  background-color: #1d4ed8;
}

.btn-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.demo-info {
  margin-top: 2rem;
  padding-top: 1.25rem;
  border-top: 1px dashed #e2e8f0;
  font-size: 0.8rem;
  color: #64748b;
  text-align: center;
}

.demo-title {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.demo-info code {
  background: #f1f5f9;
  padding: 0.15rem 0.35rem;
  border-radius: 4px;
  color: #0f172a;
}

@media (max-width: 480px) {
  .login-card {
    padding: 1.75rem 1.25rem;
  }
  .logo-title {
    font-size: 1.6rem;
  }
}
</style>