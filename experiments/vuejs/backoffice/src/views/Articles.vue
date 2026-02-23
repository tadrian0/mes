<template>
  <div class="articles">
    <h1>Articles</h1>
    <div v-if="message" class="alert" :class="messageClass">{{ message }}</div>

    <button class="btn btn-primary mb-3" @click="openModal()">Add Article</button>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Image Path</th>
          <th>Quality Control</th>
          <th>Created</th>
          <th>Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="article in articles" :key="article.ArticleID">
          <td>{{ article.ArticleID }}</td>
          <td>{{ article.Name }}</td>
          <td>{{ article.Description }}</td>
          <td>{{ article.ImagePath }}</td>
          <td>{{ article.QualityControl }}</td>
          <td>{{ article.CreatedAt }}</td>
          <td>{{ article.UpdatedAt }}</td>
          <td>
            <button class="btn btn-sm btn-warning me-2" @click="openModal(article)">Edit</button>
            <button class="btn btn-sm btn-danger" @click="deleteArticle(article.ArticleID)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div class="modal fade" id="articleModal" tabindex="-1" ref="modalElement">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit Article' : 'Add Article' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveArticle">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" v-model="form.name" required>
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" v-model="form.description"></textarea>
              </div>
              <div class="mb-3">
                <label for="imagePath" class="form-label">Image Path</label>
                <input type="text" class="form-control" id="imagePath" v-model="form.image_path">
              </div>
              <div class="mb-3">
                <label for="qualityControl" class="form-label">Quality Control</label>
                <select class="form-select" id="qualityControl" v-model="form.quality_control">
                  <option value="Pending">Pending</option>
                  <option value="Passed">Passed</option>
                  <option value="Failed">Failed</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">{{ isEditing ? 'Update' : 'Create' }}</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import api from '../services/api'
import { Modal } from 'bootstrap'

const articles = ref([])
const message = ref('')
const messageClass = ref('alert-info')
const modalElement = ref(null)
let modalInstance = null
const isEditing = ref(false)

const form = reactive({
  id: null,
  name: '',
  description: '',
  image_path: '',
  quality_control: 'Pending'
})

const fetchArticles = async () => {
  try {
    const response = await api.get('/article.php?action=list')
    if (response.data.status === 'success') {
      articles.value = response.data.data
    } else {
      showMessage('Error fetching articles: ' + response.data.message, 'alert-danger')
    }
  } catch (error) {
    showMessage('Error: ' + error.message, 'alert-danger')
  }
}

const openModal = (article = null) => {
  if (article) {
    isEditing.value = true
    form.id = article.ArticleID
    form.name = article.Name
    form.description = article.Description
    form.image_path = article.ImagePath
    form.quality_control = article.QualityControl
  } else {
    isEditing.value = false
    form.id = null
    form.name = ''
    form.description = ''
    form.image_path = ''
    form.quality_control = 'Pending'
  }
  modalInstance.show()
}

const saveArticle = async () => {
  try {
    const action = isEditing.value ? 'update' : 'create'
    const response = await api.post(`/article.php?action=${action}`, form)

    if (response.data.status === 'success') {
      showMessage(`Article ${isEditing.value ? 'updated' : 'created'} successfully.`, 'alert-success')
      fetchArticles()
      modalInstance.hide()
    } else {
      showMessage('Error: ' + response.data.message, 'alert-danger')
    }
  } catch (error) {
    showMessage('Error: ' + error.message, 'alert-danger')
  }
}

const deleteArticle = async (id) => {
  if (!confirm('Are you sure you want to delete this article?')) return

  try {
    const response = await api.post('/article.php?action=delete', { id })
    if (response.data.status === 'success') {
      showMessage('Article deleted successfully.', 'alert-success')
      fetchArticles()
    } else {
      showMessage('Error: ' + response.data.message, 'alert-danger')
    }
  } catch (error) {
    showMessage('Error: ' + error.message, 'alert-danger')
  }
}

const showMessage = (msg, cls) => {
  message.value = msg
  messageClass.value = cls
  setTimeout(() => message.value = '', 5000)
}

onMounted(() => {
  fetchArticles()
  modalInstance = new Modal(modalElement.value)
})
</script>
