import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  User,
  Mail,
  Phone,
  MapPin,
  Calendar,
  Shield,
  Bell,
  Globe,
  Camera,
  Save,

  Key,
  Settings,
  Activity,
  Award
} from "lucide-react"

export default function Profile() {
  const userInfo = {
    nom: "Jean Dupont",
    email: "jean.dupont@gescom.fr",
    telephone: "01 23 45 67 89",
    poste: "Commercial Senior",
    departement: "Ventes",
    dateEmbauche: "15/03/2020",
    adresse: "123 Rue de la Paix, 75001 Paris",
    manager: "Marie Martin",
    statut: "Actif"
  }

  const preferences = {
    langue: "Français",
    fuseauHoraire: "Europe/Paris",
    notifications: {
      email: true,
      sms: false,
      push: true
    },
    theme: "Clair"
  }

  const stats = [
    {
      title: "Commandes ce mois",
      value: "24",
      description: "+12% vs mois dernier",
      icon: Activity,
      color: "blue"
    },
    {
      title: "Visites réalisées",
      value: "18",
      description: "85% du planning",
      icon: Activity,
      color: "green"
    },
    {
      title: "CA généré",
      value: "€45,230",
      description: "+8.2% vs mois dernier",
      icon: Activity,
      color: "purple"
    },
    {
      title: "Objectifs atteints",
      value: "92%",
      description: "Excellent performance",
      icon: Award,
      color: "orange"
    }
  ]

  const getColorClasses = (color: string) => {
    const colors = {
      blue: { bg: '#dbeafe', icon: '#2563eb', border: '#3b82f6' },
      green: { bg: '#dcfce7', icon: '#16a34a', border: '#22c55e' },
      red: { bg: '#fee2e2', icon: '#dc2626', border: '#ef4444' },
      purple: { bg: '#f3e8ff', icon: '#9333ea', border: '#a855f7' },
      orange: { bg: '#fed7aa', icon: '#ea580c', border: '#f97316' }
    }
    return colors[color as keyof typeof colors] || colors.blue
  }

  return (
    <div className="page-container">
      {/* Header avec titre */}
      <div style={{ marginBottom: '3rem' }}>
        <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Mon Profil</h1>
        <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Gérez vos informations personnelles et préférences</p>
      </div>

      {/* Stats Cards */}
      <div className="stats-grid" style={{ marginBottom: '3rem' }}>
        {stats.map((stat, index) => {
          const Icon = stat.icon
          const colors = getColorClasses(stat.color)
          
          return (
            <Card key={index} style={{ 
              border: '2px solid #e5e7eb',
              backgroundColor: 'white',
              boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
              borderRadius: '0.5rem'
            }}>
              <CardContent style={{ padding: '1.5rem' }}>
                <div style={{ display: 'flex', alignItems: 'center', marginBottom: '1rem' }}>
                  <div style={{ 
                    padding: '0.75rem', 
                    borderRadius: '0.5rem', 
                    backgroundColor: colors.bg 
                  }}>
                    <Icon style={{ width: '1.5rem', height: '1.5rem', color: colors.icon }} />
                  </div>
                </div>
                <div style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', marginBottom: '0.5rem' }}>
                  {stat.value}
                </div>
                <p style={{ fontSize: '0.875rem', fontWeight: '600', color: '#1f2937', margin: '0 0 0.25rem 0' }}>
                  {stat.title}
                </p>
                <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                  {stat.description}
                </p>
              </CardContent>
            </Card>
          )
        })}
      </div>

      <div className="profile-layout">
        {/* Informations personnelles */}
        <Card style={{ 
          border: '2px solid #e5e7eb',
          backgroundColor: 'white',
          boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
          borderRadius: '0.5rem'
        }}>
          <CardHeader style={{ 
            borderBottom: '1px solid #e5e7eb', 
            backgroundColor: '#f9fafb',
            padding: '1rem 1.5rem'
          }}>
            <CardTitle style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0, display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
              <User style={{ width: '1.25rem', height: '1.25rem' }} />
              Informations personnelles
            </CardTitle>
          </CardHeader>
          <CardContent style={{ padding: '1.5rem' }}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
              {/* Photo de profil */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                <div style={{ 
                  width: '4rem', 
                  height: '4rem', 
                  backgroundColor: '#f3f4f6', 
                  borderRadius: '50%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  position: 'relative'
                }}>
                  <User style={{ width: '2rem', height: '2rem', color: '#6b7280' }} />
                  <Button 
                    size="sm" 
                    style={{ 
                      position: 'absolute', 
                      bottom: '-0.25rem', 
                      right: '-0.25rem',
                      width: '1.5rem',
                      height: '1.5rem',
                      padding: 0,
                      borderRadius: '50%',
                      backgroundColor: '#2563eb'
                    }}
                  >
                    <Camera style={{ width: '0.75rem', height: '0.75rem' }} />
                  </Button>
                </div>
                <div>
                  <h3 style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0 }}>
                    {userInfo.nom}
                  </h3>
                  <p style={{ fontSize: '0.875rem', color: '#6b7280', margin: 0 }}>
                    {userInfo.poste} - {userInfo.departement}
                  </p>
                </div>
              </div>

              {/* Champs d'informations */}
              <div style={{ display: 'grid', gap: '1rem' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <Mail style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                  <div style={{ flex: 1 }}>
                    <label style={{ fontSize: '0.75rem', fontWeight: '500', color: '#374151', display: 'block', marginBottom: '0.25rem' }}>
                      Email
                    </label>
                    <input 
                      type="email" 
                      value={userInfo.email}
                      style={{ 
                        width: '100%', 
                        padding: '0.5rem', 
                        border: '1px solid #d1d5db', 
                        borderRadius: '0.375rem',
                        fontSize: '0.875rem'
                      }}
                    />
                  </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <Phone style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                  <div style={{ flex: 1 }}>
                    <label style={{ fontSize: '0.75rem', fontWeight: '500', color: '#374151', display: 'block', marginBottom: '0.25rem' }}>
                      Téléphone
                    </label>
                    <input 
                      type="tel" 
                      value={userInfo.telephone}
                      style={{ 
                        width: '100%', 
                        padding: '0.5rem', 
                        border: '1px solid #d1d5db', 
                        borderRadius: '0.375rem',
                        fontSize: '0.875rem'
                      }}
                    />
                  </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <MapPin style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                  <div style={{ flex: 1 }}>
                    <label style={{ fontSize: '0.75rem', fontWeight: '500', color: '#374151', display: 'block', marginBottom: '0.25rem' }}>
                      Adresse
                    </label>
                    <textarea 
                      value={userInfo.adresse}
                      rows={2}
                      style={{ 
                        width: '100%', 
                        padding: '0.5rem', 
                        border: '1px solid #d1d5db', 
                        borderRadius: '0.375rem',
                        fontSize: '0.875rem',
                        resize: 'vertical'
                      }}
                    />
                  </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <Calendar style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                  <div style={{ flex: 1 }}>
                    <label style={{ fontSize: '0.75rem', fontWeight: '500', color: '#374151', display: 'block', marginBottom: '0.25rem' }}>
                      Date d'embauche
                    </label>
                    <input 
                      type="text" 
                      value={userInfo.dateEmbauche}
                      readOnly
                      style={{ 
                        width: '100%', 
                        padding: '0.5rem', 
                        border: '1px solid #d1d5db', 
                        borderRadius: '0.375rem',
                        fontSize: '0.875rem',
                        backgroundColor: '#f9fafb'
                      }}
                    />
                  </div>
                </div>
              </div>

              <Button style={{ backgroundColor: '#2563eb', color: 'white', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <Save style={{ width: '1rem', height: '1rem' }} />
                Sauvegarder les modifications
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Préférences et paramètres */}
        <Card style={{ 
          border: '2px solid #e5e7eb',
          backgroundColor: 'white',
          boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
          borderRadius: '0.5rem'
        }}>
          <CardHeader style={{ 
            borderBottom: '1px solid #e5e7eb', 
            backgroundColor: '#f9fafb',
            padding: '1rem 1.5rem'
          }}>
            <CardTitle style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0, display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
              <Settings style={{ width: '1.25rem', height: '1.25rem' }} />
              Préférences et paramètres
            </CardTitle>
          </CardHeader>
          <CardContent style={{ padding: '1.5rem' }}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
              {/* Langue et fuseau horaire */}
              <div style={{ display: 'grid', gap: '1rem' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <Globe style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                  <div style={{ flex: 1 }}>
                    <label style={{ fontSize: '0.75rem', fontWeight: '500', color: '#374151', display: 'block', marginBottom: '0.25rem' }}>
                      Langue
                    </label>
                    <select 
                      value={preferences.langue}
                      style={{ 
                        width: '100%', 
                        padding: '0.5rem', 
                        border: '1px solid #d1d5db', 
                        borderRadius: '0.375rem',
                        fontSize: '0.875rem'
                      }}
                    >
                      <option value="Français">Français</option>
                      <option value="English">English</option>
                      <option value="Español">Español</option>
                    </select>
                  </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <Calendar style={{ width: '1rem', height: '1rem', color: '#6b7280' }} />
                  <div style={{ flex: 1 }}>
                    <label style={{ fontSize: '0.75rem', fontWeight: '500', color: '#374151', display: 'block', marginBottom: '0.25rem' }}>
                      Fuseau horaire
                    </label>
                    <select 
                      value={preferences.fuseauHoraire}
                      style={{ 
                        width: '100%', 
                        padding: '0.5rem', 
                        border: '1px solid #d1d5db', 
                        borderRadius: '0.375rem',
                        fontSize: '0.875rem'
                      }}
                    >
                      <option value="Europe/Paris">Europe/Paris (UTC+1)</option>
                      <option value="Europe/London">Europe/London (UTC+0)</option>
                      <option value="America/New_York">America/New_York (UTC-5)</option>
                    </select>
                  </div>
                </div>
              </div>

              {/* Notifications */}
              <div>
                <h4 style={{ fontSize: '0.875rem', fontWeight: '600', color: '#1f2937', margin: '0 0 1rem 0', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                  <Bell style={{ width: '1rem', height: '1rem' }} />
                  Notifications
                </h4>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', cursor: 'pointer' }}>
                    <input 
                      type="checkbox" 
                      checked={preferences.notifications.email}
                      style={{ width: '1rem', height: '1rem' }}
                    />
                    <span style={{ fontSize: '0.875rem', color: '#374151' }}>Notifications par email</span>
                  </label>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', cursor: 'pointer' }}>
                    <input 
                      type="checkbox" 
                      checked={preferences.notifications.sms}
                      style={{ width: '1rem', height: '1rem' }}
                    />
                    <span style={{ fontSize: '0.875rem', color: '#374151' }}>Notifications SMS</span>
                  </label>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', cursor: 'pointer' }}>
                    <input 
                      type="checkbox" 
                      checked={preferences.notifications.push}
                      style={{ width: '1rem', height: '1rem' }}
                    />
                    <span style={{ fontSize: '0.875rem', color: '#374151' }}>Notifications push</span>
                  </label>
                </div>
              </div>

              {/* Actions rapides */}
              <div>
                <h4 style={{ fontSize: '0.875rem', fontWeight: '600', color: '#1f2937', margin: '0 0 1rem 0' }}>
                  Actions rapides
                </h4>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                  <Button variant="outline" style={{ justifyContent: 'flex-start', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <Key style={{ width: '1rem', height: '1rem' }} />
                    Changer le mot de passe
                  </Button>
                  <Button variant="outline" style={{ justifyContent: 'flex-start', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <Shield style={{ width: '1rem', height: '1rem' }} />
                    Paramètres de sécurité
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
