<template>
  <div class="dashboard-page">
    <!-- Navbar Atas -->
    <header class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <span class="brand-icon">📚</span>
          <span class="brand-name">MyBook Catalog</span>
        </div>
        <div class="navbar-user">
          <span class="user-greeting">Halo, <strong>{{ currentUser.name }}</strong></span>
          <button @click="handleLogout" class="btn-logout">Keluar</button>
        </div>
      </div>
    </header>

    <!-- Konten Utama -->
    <main class="main-container">
      <!-- Toast Notifikasi -->
      <div v-if="toastMessage" :class="['toast-banner', toastType === 'success' ? 'toast-success' : 'toast-error']">
        {{ toastMessage }}
      </div>

      <!-- Header Aksi & Statistik -->
      <div class="content-header">
        <div>
          <h2 class="content-title">Koleksi Buku & Komik</h2>
          <p class="content-subtitle">Kelola daftar bacaan novel, komik, dan antologi pribadi</p>
        </div>
        <button @click="openCreateModal" class="btn-add">
          + Tambah Buku
        </button>
      </div>

      <!-- Ringkasan Statistik -->
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-label">Total Koleksi</span>
          <span class="stat-value">{{ books.length }}</span>
        </div>
        <div class="stat-card">
          <span class="stat-label">Sedang Dibaca</span>
          <span class="stat-value text-amber">{{ countByStatus('reading') }}</span>
        </div>
        <div class="stat-card">
          <span class="stat-label">Selesai Dibaca</span>
          <span class="stat-value text-emerald">{{ countByStatus('completed') }}</span>
        </div>
      </div>

      <!-- Tabel Katalog Buku -->
      <div class="table-card">
        <div v-if="loading" class="state-box">
          Memuat data katalog buku...
        </div>

        <div v-else-if="books.length === 0" class="state-box">
          Belum ada buku dalam katalog. Klik tombol <strong>+ Tambah Buku</strong> untuk memulai!
        </div>

        <div v-else class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 60px; text-align: center;">No</th>
                <th style="min-width: 260px;">Judul Buku</th>
                <th style="min-width: 200px;">Penulis / Mangaka</th>
                <th style="width: 130px; text-align: center;">Tipe</th>
                <th style="width: 100px; text-align: center;">Tahun</th>
                <th style="width: 180px; min-width: 160px; text-align: center;">Status Baca</th>
                <th style="width: 150px; text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(book, index) in books" :key="book.id">
                <td style="text-align: center;">{{ index + 1 }}</td>
                <td class="font-bold">{{ book.title }}</td>
                <td>{{ book.author }}</td>
                <td style="text-align: center; white-space: nowrap;">
                  <span :class="['badge', 'badge-type', 'type-' + book.type]">
                    {{ formatType(book.type) }}
                  </span>
                </td>
                <td style="text-align: center; white-space: nowrap;">{{ book.published_year }}</td>
                <td style="text-align: center; white-space: nowrap;">
                  <span :class="['badge', 'badge-status', 'status-' + book.status]">
                    {{ formatStatus(book.status) }}
                  </span>
                </td>
                <td class="action-cell" style="white-space: nowrap;">
                  <button @click="openEditModal(book)" class="btn-action btn-edit" title="Edit">
                    Ubah
                  </button>
                  <button @click="openDeleteModal(book)" class="btn-action btn-delete" title="Hapus">
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-dialog">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Ubah Data Buku' : 'Tambah Buku Baru' }}</h3>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>

        <div v-if="modalError" class="modal-error">
          {{ modalError }}
        </div>

        <form @submit.prevent="saveBook" class="modal-form">
          <div class="form-group">
            <label for="book-title">Judul Buku / Komik *</label>
            <input
              id="book-title"
              v-model="form.title"
              type="text"
              required
              class="form-control"
              placeholder="Contoh: Solo Leveling Vol. 1"
            />
          </div>

          <div class="form-group">
            <label for="book-author">Penulis / Mangaka *</label>
            <input
              id="book-author"
              v-model="form.author"
              type="text"
              required
              class="form-control"
              placeholder="Contoh: Chugong"
            />
          </div>

          <div class="form-row">
            <div class="form-group flex-1">
              <label for="book-type">Tipe Karya *</label>
              <select id="book-type" v-model="form.type" required class="form-control">
                <option value="comic">Komik / Manga</option>
                <option value="novel">Novel</option>
                <option value="anthology">Antologi / Non-Fiksi</option>
              </select>
            </div>

            <div class="form-group flex-1">
              <label for="book-year">Tahun Terbit *</label>
              <input
                id="book-year"
                v-model.number="form.published_year"
                type="number"
                min="1800"
                :max="new Date().getFullYear() + 1"
                required
                class="form-control"
              />
            </div>
          </div>

          <div class="form-group">
            <label for="book-status">Status Baca *</label>
            <select id="book-status" v-model="form.status" required class="form-control">
              <option value="unread">Belum Dibaca (Unread)</option>
              <option value="reading">Sedang Dibaca (Reading)</option>
              <option value="completed">Selesai (Completed)</option>
            </select>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="saving" class="btn-primary">
              {{ saving ? 'Menyimpan...' : (isEditing ? 'Perbarui Data' : 'Simpan Data') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus Buku -->
    <div v-if="showDeleteModal" class="modal-backdrop" @click.self="closeDeleteModal">
      <div class="modal-dialog modal-delete-dialog">
        <div class="delete-icon-wrapper">
          <span class="delete-icon">⚠️</span>
        </div>
        <h3 class="delete-title">Hapus Buku?</h3>
        <p class="delete-desc">
          Apakah Anda yakin ingin menghapus buku <strong>"{{ bookToDelete?.title }}"</strong>? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
        </p>

        <div class="delete-modal-footer">
          <button type="button" @click="closeDeleteModal" class="btn-secondary" :disabled="deleting">
            Batal
          </button>
          <button type="button" @click="confirmDelete" class="btn-danger" :disabled="deleting">
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '../services/api';

const router = useRouter();
const books = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalError = ref('');
const toastMessage = ref('');
const toastType = ref('success');

// State Modal Hapus
const showDeleteModal = ref(false);
const bookToDelete = ref(null);
const deleting = ref(false);

const currentUser = ref(
  JSON.parse(localStorage.getItem('mybook_user') || '{"name": "Pengguna MyBook"}')
);

const form = reactive({
  title: '',
  author: '',
  type: 'comic',
  published_year: new Date().getFullYear(),
  status: 'unread',
});

const showToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 3500);
};

const fetchBooks = async () => {
  loading.value = true;
  try {
    const res = await apiClient.get('/books');
    books.value = res.data.data;
  } catch (error) {
    showToast('Gagal mengambil data buku.', 'error');
  } finally {
    loading.value = false;
  }
};

const countByStatus = (status) => {
  return books.value.filter((b) => b.status === status).length;
};

const formatType = (type) => {
  const map = { comic: 'Komik', novel: 'Novel', anthology: 'Antologi' };
  return map[type] || type;
};

const formatStatus = (status) => {
  const map = { unread: 'Belum Dibaca', reading: 'Sedang Dibaca', completed: 'Selesai' };
  return map[status] || status;
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.title = '';
  form.author = '';
  form.type = 'comic';
  form.published_year = new Date().getFullYear();
  form.status = 'unread';
  modalError.value = '';
  showModal.value = true;
};

const openEditModal = (book) => {
  isEditing.value = true;
  editingId.value = book.id;
  form.title = book.title;
  form.author = book.author;
  form.type = book.type;
  form.published_year = book.published_year;
  form.status = book.status;
  modalError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveBook = async () => {
  saving.value = true;
  modalError.value = '';

  try {
    if (isEditing.value) {
      await apiClient.put(`/books/${editingId.value}`, form);
      showToast('Data buku berhasil diperbarui!');
    } else {
      await apiClient.post('/books', form);
      showToast('Buku baru berhasil ditambahkan!');
    }
    closeModal();
    await fetchBooks();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      modalError.value = 'Validasi gagal: pastikan semua kolom terisi dengan benar.';
    } else {
      modalError.value = 'Terjadi kesalahan saat menyimpan data.';
    }
  } finally {
    saving.value = false;
  }
};

const openDeleteModal = (book) => {
  bookToDelete.value = book;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  if (!deleting.value) {
    showDeleteModal.value = false;
    bookToDelete.value = null;
  }
};

const confirmDelete = async () => {
  if (!bookToDelete.value) return;
  deleting.value = true;

  try {
    await apiClient.delete(`/books/${bookToDelete.value.id}`);
    showToast(`Buku "${bookToDelete.value.title}" berhasil dihapus.`);
    closeDeleteModal();
    await fetchBooks();
  } catch (error) {
    showToast('Gagal menghapus buku.', 'error');
  } finally {
    deleting.value = false;
  }
};

const handleLogout = async () => {
  try {
    await apiClient.post('/logout');
  } catch (e) {
    // Abaikan jika token kedaluwarsa di server
  } finally {
    localStorage.removeItem('mybook_token');
    localStorage.removeItem('mybook_user');
    router.push('/login');
  }
};

onMounted(() => {
  fetchBooks();
});
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  background-color: #f8fafc;
}

.navbar {
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  padding: 0.85rem 1.5rem;
}

.navbar-container {
  max-width: 1600px;
  width: 95%;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.navbar-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-greeting {
  font-size: 0.9rem;
  color: #475569;
}

.btn-logout {
  padding: 0.4rem 0.85rem;
  background-color: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-logout:hover {
  background-color: #fca5a5;
}

.main-container {
  max-width: 1600px;
  width: 95%;
  margin: 2rem auto;
  padding: 0 1.5rem;
}

.toast-banner {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  font-weight: 500;
}

.toast-success {
  background-color: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

.toast-error {
  background-color: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.content-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.content-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
}

.content-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.btn-add {
  padding: 0.65rem 1.25rem;
  background-color: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-add:hover {
  background-color: #1d4ed8;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: #ffffff;
  padding: 1.25rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
  text-transform: uppercase;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.text-amber {
  color: #d97706;
}

.text-emerald {
  color: #059669;
}

.table-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.9rem;
}

.data-table th {
  background-color: #f8fafc;
  padding: 1rem 1.25rem;
  color: #475569;
  font-weight: 600;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.data-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
  vertical-align: middle;
}

.data-table tr:hover {
  background-color: #f8fafc;
}

.font-bold {
  font-weight: 600;
  color: #0f172a;
}

.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap !important;
  word-break: keep-all !important;
  text-overflow: clip;
  padding: 0.35rem 0.85rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.01em;
  line-height: 1.25;
}

.type-comic {
  background-color: #f3e8ff;
  color: #7e22ce;
}

.type-novel {
  background-color: #e0f2fe;
  color: #0369a1;
}

.type-anthology {
  background-color: #ffedd5;
  color: #c2410c;
}

.status-unread {
  background-color: #f1f5f9;
  color: #64748b;
}

.status-reading {
  background-color: #fef3c7;
  color: #b45309;
}

.status-completed {
  background-color: #dcfce7;
  color: #15803d;
}

.action-cell {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-action {
  padding: 0.35rem 0.65rem;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-action:hover {
  opacity: 0.85;
}

.btn-edit {
  background-color: #e0e7ff;
  color: #4338ca;
}

.btn-delete {
  background-color: #fee2e2;
  color: #b91c1c;
}

.state-box {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #64748b;
  font-size: 0.95rem;
}

/* Modal Styling */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(15, 23, 42, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
  z-index: 50;
}

.modal-dialog {
  background: #ffffff;
  width: 100%;
  max-width: 500px;
  border-radius: 12px;
  padding: 1.75rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
}

.modal-close {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: #94a3b8;
  cursor: pointer;
}

.modal-error {
  background-color: #fef2f2;
  color: #b91c1c;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 0.825rem;
  margin-bottom: 1rem;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-group label {
  font-size: 0.825rem;
  font-weight: 600;
  color: #334155;
}

.form-control {
  width: 100%;
  padding: 0.65rem 0.85rem;
  background-color: #ffffff;
  color: #0f172a;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.form-row {
  display: flex;
  gap: 1rem;
}

.flex-1 {
  flex: 1;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.btn-secondary {
  padding: 0.6rem 1rem;
  background-color: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary {
  padding: 0.6rem 1.2rem;
  background-color: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
}

/* Modal Konfirmasi Hapus Styling */
.modal-delete-dialog {
  max-width: 440px;
  text-align: center;
  padding: 2rem 1.75rem;
}

.delete-icon-wrapper {
  width: 56px;
  height: 56px;
  margin: 0 auto 1.25rem;
  background-color: #fee2e2;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
}

.delete-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.6rem;
}

.delete-desc {
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 1.75rem;
}

.delete-desc strong {
  color: #1e293b;
}

.delete-modal-footer {
  display: flex;
  justify-content: center;
  gap: 0.85rem;
}

.btn-danger {
  padding: 0.6rem 1.25rem;
  background-color: #dc2626;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s, opacity 0.2s;
}

.btn-danger:hover:not(:disabled) {
  background-color: #b91c1c;
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsif Mobile & Tablet */
@media (max-width: 768px) {
  .navbar-container {
    padding: 0.75rem 1rem;
  }
  .main-container {
    padding: 0 1rem;
    margin: 1.25rem auto;
  }
  .content-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  .btn-add {
    width: 100%;
    text-align: center;
  }
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .form-row {
    flex-direction: column;
    gap: 0.75rem;
  }
  .modal-dialog {
    max-width: 95%;
    padding: 1.25rem;
  }
}
</style>