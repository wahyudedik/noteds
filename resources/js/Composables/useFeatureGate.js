import { usePage } from '@inertiajs/vue3'

export function useFeatureGate() {
  const page = usePage()
  const user = page.props?.auth?.user || null

  const hasRole = (role) => !!user && user.role === role

  const can = (feature) => {
    switch (feature) {
      case 'admin':
        return hasRole('admin')
      case 'privacy.dashboard':
      case 'activity.log':
        return hasRole('admin')
      default:
        return !!user
    }
  }

  return { user, hasRole, can }
}
