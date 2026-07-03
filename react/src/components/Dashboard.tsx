import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { 
  BarChart3, 
  Users, 
  ShoppingCart, 
  TrendingUp,
  Calendar,
  MessageSquare,
  ArrowUpRight,
  ArrowDownRight
} from "lucide-react"

export default function Dashboard() {
  const stats = [
    {
      title: "Commandes du jour",
      value: "24",
      description: "+12% par rapport à hier",
      icon: ShoppingCart,
      trend: "up",
      color: "blue"
    },
    {
      title: "Visites du jour",
      value: "8",
      description: "3 visites restantes",
      icon: Calendar,
      trend: "down",
      color: "green"
    },
    {
      title: "Nouveaux prospects",
      value: "5",
      description: "+2 cette semaine",
      icon: Users,
      trend: "up",
      color: "purple"
    },
    {
      title: "CA du mois",
      value: "€45,230",
      description: "+8.2% vs mois dernier",
      icon: TrendingUp,
      trend: "up",
      color: "orange"
    }
  ]

  const quickActions = [
    {
      title: "Nouvelle commande",
      icon: ShoppingCart,
      color: "blue"
    },
    {
      title: "Planifier visite",
      icon: Calendar,
      color: "green"
    },
    {
      title: "Ajouter prospect",
      icon: Users,
      color: "purple"
    },
    {
      title: "Envoyer message",
      icon: MessageSquare,
      color: "orange"
    }
  ]

  return (
    <div className="page-container">
      {/* Header avec titre et bouton */}
      <div className="page-header">
        <div>
          <h1 style={{ fontSize: '2rem', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>Tableau de bord</h1>
          <p style={{ color: '#6b7280', margin: '0.5rem 0 0 0' }}>Bienvenue dans votre CRM moderne</p>
        </div>
        <div className="page-header-buttons" style={{ display: 'flex', gap: '1rem' }}>
          <Button variant="outline" style={{ borderColor: '#d1d5db', color: '#374151' }}>
            <Calendar style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Aujourd'hui
          </Button>
          <Button style={{ backgroundColor: '#2563eb', color: 'white' }}>
            <BarChart3 style={{ marginRight: '0.5rem', width: '1rem', height: '1rem' }} />
            Statistiques
          </Button>
        </div>
      </div>

      {/* Stats Cards avec espacement forcé */}
      <div className="stats-grid" style={{ marginBottom: '3rem' }}>
        {stats.map((stat, index) => {
          const Icon = stat.icon
          const TrendIcon = stat.trend === "up" ? ArrowUpRight : ArrowDownRight
          
          const colors = {
            blue: { bg: '#dbeafe', icon: '#2563eb', border: '#3b82f6', trend: '#2563eb' },
            green: { bg: '#dcfce7', icon: '#16a34a', border: '#22c55e', trend: '#16a34a' },
            purple: { bg: '#f3e8ff', icon: '#9333ea', border: '#a855f7', trend: '#9333ea' },
            orange: { bg: '#fed7aa', icon: '#ea580c', border: '#f97316', trend: '#ea580c' }
          }
          
          const colorScheme = colors[stat.color as keyof typeof colors] || colors.blue
          
          return (
            <Card key={index} style={{ 
              border: '2px solid #e5e7eb',
              backgroundColor: 'white',
              boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
              borderRadius: '0.5rem'
            }}>
              <CardContent style={{ padding: '1.5rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
                  <div style={{ 
                    padding: '0.75rem', 
                    borderRadius: '0.5rem', 
                    backgroundColor: colorScheme.bg 
                  }}>
                    <Icon style={{ width: '1.5rem', height: '1.5rem', color: colorScheme.icon }} />
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem', color: colorScheme.trend }}>
                    <TrendIcon style={{ width: '1rem', height: '1rem' }} />
                    <span style={{ fontSize: '0.875rem', fontWeight: '500' }}>+12%</span>
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

      {/* Quick Actions avec espacement forcé */}
      <div style={{ marginBottom: '3rem' }}>
        <h2 style={{ fontSize: '1.25rem', fontWeight: '600', color: '#1f2937', marginBottom: '1.5rem' }}>
          Actions rapides
        </h2>
        <div className="quick-actions-grid">
          {quickActions.map((action, index) => {
            const Icon = action.icon
            
            const colors = {
              blue: { bg: '#dbeafe', icon: '#2563eb', border: '#3b82f6' },
              green: { bg: '#dcfce7', icon: '#16a34a', border: '#22c55e' },
              purple: { bg: '#f3e8ff', icon: '#9333ea', border: '#a855f7' },
              orange: { bg: '#fed7aa', icon: '#ea580c', border: '#f97316' }
            }
            
            const colorScheme = colors[action.color as keyof typeof colors] || colors.blue
            
            return (
              <Card key={index} style={{ 
                border: '2px solid #e5e7eb',
                backgroundColor: 'white',
                boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                borderRadius: '0.5rem',
                cursor: 'pointer',
                transition: 'all 0.3s ease'
              }}>
                <CardContent style={{ padding: '1.5rem' }}>
                  <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center', gap: '1rem' }}>
                    <div style={{ 
                      padding: '1rem', 
                      borderRadius: '0.5rem', 
                      backgroundColor: colorScheme.bg 
                    }}>
                      <Icon style={{ width: '2rem', height: '2rem', color: colorScheme.icon }} />
                    </div>
                    <div>
                      <h3 style={{ fontSize: '0.875rem', fontWeight: '600', color: '#1f2937', margin: 0 }}>
                        {action.title}
                      </h3>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )
          })}
        </div>
      </div>

      {/* Recent Activity et Prochaines échéances */}
      <div className="activity-grid">
        <Card style={{ 
          border: '2px solid #e5e7eb',
          backgroundColor: 'white',
          boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
          borderRadius: '0.5rem'
        }}>
          <CardHeader style={{ 
            borderBottom: '1px solid #e5e7eb', 
            backgroundColor: '#f9fafb',
            padding: '1rem 1.5rem'
          }}>
            <CardTitle style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0 }}>
              Activité récente
            </CardTitle>
          </CardHeader>
          <CardContent style={{ padding: '1.5rem' }}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                <div style={{ padding: '0.5rem', borderRadius: '50%', backgroundColor: '#dcfce7' }}>
                  <ShoppingCart style={{ width: '1rem', height: '1rem', color: '#16a34a' }} />
                </div>
                <div style={{ flex: 1 }}>
                  <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#1f2937', margin: 0 }}>
                    Commande #1234 créée
                  </p>
                  <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                    Il y a 2 heures
                  </p>
                </div>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                <div style={{ padding: '0.5rem', borderRadius: '50%', backgroundColor: '#dbeafe' }}>
                  <Calendar style={{ width: '1rem', height: '1rem', color: '#2563eb' }} />
                </div>
                <div style={{ flex: 1 }}>
                  <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#1f2937', margin: 0 }}>
                    Visite planifiée chez Client ABC
                  </p>
                  <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                    Il y a 4 heures
                  </p>
                </div>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                <div style={{ padding: '0.5rem', borderRadius: '50%', backgroundColor: '#f3e8ff' }}>
                  <Users style={{ width: '1rem', height: '1rem', color: '#9333ea' }} />
                </div>
                <div style={{ flex: 1 }}>
                  <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#1f2937', margin: 0 }}>
                    Nouveau prospect ajouté
                  </p>
                  <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                    Hier
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card style={{ 
          border: '2px solid #e5e7eb',
          backgroundColor: 'white',
          boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
          borderRadius: '0.5rem'
        }}>
          <CardHeader style={{ 
            borderBottom: '1px solid #e5e7eb', 
            backgroundColor: '#f9fafb',
            padding: '1rem 1.5rem'
          }}>
            <CardTitle style={{ fontSize: '1.125rem', fontWeight: '600', color: '#1f2937', margin: 0 }}>
              Prochaines échéances
            </CardTitle>
          </CardHeader>
          <CardContent style={{ padding: '1.5rem' }}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <div style={{ width: '0.5rem', height: '0.5rem', backgroundColor: '#ef4444', borderRadius: '50%' }}></div>
                  <div>
                    <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#1f2937', margin: 0 }}>
                      Facture #1234
                    </p>
                    <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                      Client ABC - €2,500
                    </p>
                  </div>
                </div>
                <span style={{ fontSize: '0.75rem', color: '#ef4444', fontWeight: '500' }}>Échue</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <div style={{ width: '0.5rem', height: '0.5rem', backgroundColor: '#f59e0b', borderRadius: '50%' }}></div>
                  <div>
                    <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#1f2937', margin: 0 }}>
                      Facture #1235
                    </p>
                    <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                      Client XYZ - €1,800
                    </p>
                  </div>
                </div>
                <span style={{ fontSize: '0.75rem', color: '#f59e0b', fontWeight: '500' }}>Dans 2 jours</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <div style={{ width: '0.5rem', height: '0.5rem', backgroundColor: '#22c55e', borderRadius: '50%' }}></div>
                  <div>
                    <p style={{ fontSize: '0.875rem', fontWeight: '500', color: '#1f2937', margin: 0 }}>
                      Facture #1236
                    </p>
                    <p style={{ fontSize: '0.75rem', color: '#6b7280', margin: 0 }}>
                      Client DEF - €3,200
                    </p>
                  </div>
                </div>
                <span style={{ fontSize: '0.75rem', color: '#22c55e', fontWeight: '500' }}>Dans 5 jours</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

