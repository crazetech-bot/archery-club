import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Lightweight form helper that wraps Inertia router calls with loading/error state.
 *
 * @param {Object} initialData - Initial form field values
 */
export function useForm(initialData = {}) {
  const data     = reactive({ ...initialData })
  const errors   = reactive({})
  const loading  = ref(false)
  const wasSuccessful = ref(false)

  const isDirty = computed(() => {
    return Object.keys(initialData).some(
      (key) => data[key] !== initialData[key]
    )
  })

  function clearErrors() {
    Object.keys(errors).forEach((k) => delete errors[k])
  }

  function reset() {
    Object.assign(data, initialData)
    clearErrors()
    wasSuccessful.value = false
  }

  function setErrors(newErrors) {
    clearErrors()
    Object.assign(errors, newErrors)
  }

  /**
   * Submit the form via Inertia.
   * @param {string} method - 'post'|'put'|'patch'|'delete'
   * @param {string} url
   * @param {Object} options - Additional Inertia visit options
   */
  function submit(method, url, options = {}) {
    loading.value = true
    wasSuccessful.value = false
    clearErrors()

    const { onSuccess, onError, onFinish, ...restOptions } = options

    router[method](url, { ...data }, {
      preserveScroll: true,
      ...restOptions,
      onSuccess: (page) => {
        wasSuccessful.value = true
        onSuccess?.(page)
      },
      onError: (err) => {
        setErrors(err)
        onError?.(err)
      },
      onFinish: () => {
        loading.value = false
        onFinish?.()
      },
    })
  }

  const post   = (url, options) => submit('post',   url, options)
  const put    = (url, options) => submit('put',    url, options)
  const patch  = (url, options) => submit('patch',  url, options)
  const del    = (url, options) => submit('delete', url, options)

  return {
    data,
    errors,
    loading,
    wasSuccessful,
    isDirty,
    reset,
    clearErrors,
    setErrors,
    submit,
    post,
    put,
    patch,
    delete: del,
  }
}
