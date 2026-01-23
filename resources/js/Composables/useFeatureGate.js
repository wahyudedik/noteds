import { usePage } from '@inertiajs/vue3'

export function useFeatureGate() {
  const page = usePage()
  const user = page.props?.auth?.user || null

  const hasRole = (role) => !!user && (user.role === role || user.clipper_role === role)

  const can = (feature) => {
    switch (feature) {
      case 'admin':
        return hasRole('admin')
      case 'clipper':
      case 'brand':
        return hasRole('clipper') || hasRole('brand') || hasRole('admin')
      case 'marketplace.seller':
        return !!user && (user.is_verified_seller || hasRole('admin'))
      case 'marketplace.buyer':
        return !!user
      case 'stocks':
        return !!user
      case 'privacy.dashboard':
      case 'activity.log':
        return hasRole('admin')
      default:
        return !!user
    }
  }

  return { user, hasRole, can }
}
