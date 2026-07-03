import { useState } from "react"
import { Link } from "react-router-dom"
import { Button } from "@/components/ui/button"
import "./Navigation.css"
import { useAuth } from "../hooks/useAuthContext"
import { 
  ChevronDown,
  Users,
  LogOut,
  User,
  Network,
  Briefcase,
  Satellite,
  Menu,
  X
} from "lucide-react"

export default function Navigation() {
  const [activeDropdown, setActiveDropdown] = useState<string | null>(null)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const { user, logout } = useAuth()

  const handleLogout = async () => {
    try {
      await logout()
      setActiveDropdown(null)
      setIsMobileMenuOpen(false)
    } catch (error) {
      console.error('Erreur lors de la déconnexion:', error)
    }
  }

  const menuSections = [
    {
      title: "Référentiels",
      icon: Network,
      items: [
        { name: "Produits", href: "/Produits" },
        { name: "Clients", href: "/Magasins" },
        { name: "Utilisateurs", href: "/Refentiel_Utilisateurs" },
        { name: "Produits PEM", href: "/Produits-PEM" },
        { name: "Strats PEM", href: "/Strats-PEM" }
      ]
    },
    {
      title: "Promoteurs",
      icon: Users,
      items: [
        { name: "Commandes", href: "/Commandes" },
        { name: "Commandes Juva", href: "/Commandesjuva" },
        { name: "Visites", href: "/Visites" },
        { name: "Visites Juva", href: "/Visitesjuva" },
        { name: "Planning", href: "/Planning" },
        { name: "Validation Planning", href: "/ValidationPlanning" }
      ]
    },
    {
      title: "CS",
      icon: Briefcase,
      items: [
        { name: "Prospects en cours", href: "/Prospects" },
        { name: "Visite prospection", href: "/Prospections" },
        { name: "Tâche prospection", href: "/PlanningProspection" },
        { name: "Tâche commercial", href: "/PlanningProspection/Clients" },
        { name: "Gestion CA", href: "/ProspectionGestionCA" },
        { name: "Visites commerciales", href: "/VisitesCS" }
      ]
    },
    {
      title: "Autre",
      icon: Satellite,
      items: [
        { name: "News ABC", href: "/News" },
        { name: "Jours OFF", href: "/JoursOFF" },
        { name: "Traductions", href: "/Traductions" }
      ]
    }
  ]

  const toggleDropdown = (sectionTitle: string) => {
    setActiveDropdown(activeDropdown === sectionTitle ? null : sectionTitle)
  }

  return (
    <header style={{ 
      position: 'sticky', 
      top: 0, 
      zIndex: 50, 
      width: '100%', 
      borderBottom: '1px solid #e5e7eb', 
      backgroundColor: 'white', 
      boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1)' 
    }}>
      <div style={{ maxWidth: '1200px', margin: '0 auto', padding: '0 1rem' }}>
        <div style={{ 
          display: 'flex', 
          height: '4rem', 
          alignItems: 'center', 
          justifyContent: 'space-between' 
        }}>
          {/* Logo */}
          <Link to="/" style={{ textDecoration: 'none' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', cursor: 'pointer' }}>
              <img 
                src="./logo.png" 
                alt="ABC Logo" 
                style={{ 
                  height: '2.5rem', 
                  width: '2.5rem', 
                  objectFit: 'contain'
                }} 
              />
              <span style={{ fontSize: '1.25rem', fontWeight: 'bold', color: '#1f2937' }}>
                CRM ABC
              </span>
            </div>
          </Link>

          {/* Mobile Menu Button */}
          <Button
            variant="ghost"
            size="sm"
            className="mobile-menu-button"
            style={{ 
              padding: '0.5rem',
              color: '#374151'
            }}
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          >
            {isMobileMenuOpen ? <X size={20} /> : <Menu size={20} />}
          </Button>

          {/* Desktop Navigation */}
          <nav className="desktop-nav" style={{ 
            alignItems: 'center', 
            gap: '0.25rem'
          }}>
            {menuSections.map((section, index) => {
              const Icon = section.icon
              return (
                <div key={index} style={{ position: 'relative' }}>
                  <Button
                    variant="ghost"
                    style={{ 
                      display: 'flex', 
                      alignItems: 'center', 
                      gap: '0.5rem', 
                      color: '#374151',
                      padding: '0.5rem 1rem',
                      borderRadius: '0.375rem',
                      backgroundColor: 'transparent',
                      border: 'none',
                      cursor: 'pointer',
                      transition: 'all 0.2s ease'
                    }}
                    onMouseEnter={(e) => {
                      e.currentTarget.style.color = '#2563eb'
                      e.currentTarget.style.backgroundColor = '#eff6ff'
                    }}
                    onMouseLeave={(e) => {
                      e.currentTarget.style.color = '#374151'
                      e.currentTarget.style.backgroundColor = 'transparent'
                    }}
                    onClick={() => toggleDropdown(section.title)}
                  >
                    <Icon style={{ height: '1rem', width: '1rem' }} />
                    <span>{section.title}</span>
                    <ChevronDown style={{ 
                      height: '1rem', 
                      width: '1rem', 
                      transition: 'transform 0.2s ease',
                      transform: activeDropdown === section.title ? 'rotate(180deg)' : 'rotate(0deg)'
                    }} />
                  </Button>
                  
                  {/* Dropdown Menu */}
                  {activeDropdown === section.title && (
                    <div style={{ 
                      position: 'absolute', 
                      top: '100%', 
                      left: 0, 
                      marginTop: '0.25rem', 
                      width: '16rem', 
                      backgroundColor: 'white', 
                      border: '1px solid #e5e7eb', 
                      borderRadius: '0.5rem', 
                      boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                      zIndex: 50
                    }}>
                      <div style={{ padding: '0.25rem' }}>
                        {section.items.map((item, itemIndex) => (
                          <Link
                            key={itemIndex}
                            to={item.href}
                            style={{ 
                              display: 'block', 
                              padding: '0.5rem 1rem', 
                              fontSize: '0.875rem', 
                              color: '#374151',
                              textDecoration: 'none',
                              borderRadius: '0.375rem',
                              transition: 'all 0.2s ease'
                            }}
                            onMouseEnter={(e) => {
                              e.currentTarget.style.backgroundColor = '#f3f4f6'
                              e.currentTarget.style.color = '#2563eb'
                            }}
                            onMouseLeave={(e) => {
                              e.currentTarget.style.backgroundColor = 'transparent'
                              e.currentTarget.style.color = '#374151'
                            }}
                            onClick={() => setActiveDropdown(null)}
                          >
                            {item.name}
                          </Link>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              )
            })}
          </nav>

          {/* Mobile Navigation */}
          {isMobileMenuOpen && (
            <div style={{
              position: 'absolute',
              top: '100%',
              left: 0,
              right: 0,
              backgroundColor: 'white',
              borderBottom: '1px solid #e5e7eb',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
              zIndex: 40
            }}>
              <div style={{ padding: '1rem' }}>
                {menuSections.map((section, index) => {
                  const Icon = section.icon
                  return (
                    <div key={index} style={{ marginBottom: '1rem' }}>
                      <div style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.5rem',
                        padding: '0.75rem',
                        backgroundColor: '#f9fafb',
                        borderRadius: '0.5rem',
                        marginBottom: '0.5rem',
                        fontWeight: '600',
                        color: '#374151'
                      }}>
                        <Icon style={{ height: '1rem', width: '1rem' }} />
                        {section.title}
                      </div>
                      <div style={{ paddingLeft: '1rem' }}>
                        {section.items.map((item, itemIndex) => (
                          <Link
                            key={itemIndex}
                            to={item.href}
                            style={{
                              display: 'block',
                              padding: '0.5rem 0.75rem',
                              color: '#6b7280',
                              textDecoration: 'none',
                              borderRadius: '0.375rem',
                              marginBottom: '0.25rem',
                              transition: 'all 0.2s ease'
                            }}
                            onMouseEnter={(e) => {
                              e.currentTarget.style.backgroundColor = '#f3f4f6'
                              e.currentTarget.style.color = '#2563eb'
                            }}
                            onMouseLeave={(e) => {
                              e.currentTarget.style.backgroundColor = 'transparent'
                              e.currentTarget.style.color = '#6b7280'
                            }}
                            onClick={() => setIsMobileMenuOpen(false)}
                          >
                            {item.name}
                          </Link>
                        ))}
                      </div>
                    </div>
                  )
                })}
                {/* Mobile User Menu */}
                <div style={{ borderTop: '1px solid #e5e7eb', paddingTop: '1rem', marginTop: '1rem' }}>
                  {/* Nom d'utilisateur mobile */}
                  {user && (
                    <div style={{
                      padding: '0.75rem',
                      backgroundColor: '#f9fafb',
                      borderRadius: '0.375rem',
                      marginBottom: '0.5rem',
                      fontSize: '0.875rem',
                      color: '#6b7280',
                      fontWeight: '500'
                    }}>
                      Bonjour, {user.displayname || user.login}
                    </div>
                  )}
                  
                  <Link 
                    to="/Profile"
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '0.5rem',
                      padding: '0.75rem',
                      color: '#374151',
                      textDecoration: 'none',
                      borderRadius: '0.375rem',
                      marginBottom: '0.5rem',
                      transition: 'all 0.2s ease'
                    }}
                    onMouseEnter={(e) => {
                      e.currentTarget.style.backgroundColor = '#f3f4f6'
                      e.currentTarget.style.color = '#2563eb'
                    }}
                    onMouseLeave={(e) => {
                      e.currentTarget.style.backgroundColor = 'transparent'
                      e.currentTarget.style.color = '#374151'
                    }}
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <User style={{ height: '1rem', width: '1rem' }} />
                    
                  </Link>
                  <Button 
                    variant="ghost" 
                    style={{
                      width: '100%',
                      justifyContent: 'flex-start',
                      color: '#dc2626',
                      padding: '0.75rem',
                      borderRadius: '0.375rem'
                    }}
                    onClick={handleLogout}
                  >
                    <LogOut style={{ height: '1rem', width: '1rem', marginRight: '0.5rem' }} />
                    Déconnexion
                  </Button>
                </div>
              </div>
            </div>
          )}

          {/* User Menu */}
          <div className="desktop-user-menu" style={{ 
            alignItems: 'center', 
            gap: '1rem'
          }}>
            {/* Nom d'utilisateur */}
            {user && (
              <span style={{ 
                fontSize: '0.875rem', 
                color: '#6b7280',
                fontWeight: '500'
              }}>
                Bonjour, {user.displayname || user.login}
              </span>
            )}
            
            <Link 
              to="/Profile"
              style={{ 
                color: '#374151',
                display: 'flex',
                alignItems: 'center',
                gap: '0.5rem',
                padding: '0.5rem 1rem',
                backgroundColor: 'transparent',
                border: 'none',
                borderRadius: '0.375rem',
                cursor: 'pointer',
                transition: 'all 0.2s ease',
                textDecoration: 'none'
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.color = '#2563eb'
                e.currentTarget.style.backgroundColor = '#eff6ff'
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.color = '#374151'
                e.currentTarget.style.backgroundColor = 'transparent'
              }}
            >
              <User style={{ height: '1rem', width: '1rem' }} />
            </Link>
            <Button 
              variant="ghost" 
              size="sm" 
              onClick={handleLogout}
              style={{ 
                color: '#374151',
                display: 'flex',
                alignItems: 'center',
                gap: '0.5rem',
                padding: '0.5rem 1rem',
                backgroundColor: 'transparent',
                border: 'none',
                borderRadius: '0.375rem',
                cursor: 'pointer',
                transition: 'all 0.2s ease'
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.color = '#dc2626'
                e.currentTarget.style.backgroundColor = '#fef2f2'
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.color = '#374151'
                e.currentTarget.style.backgroundColor = 'transparent'
              }}
            >
              <LogOut style={{ height: '1rem', width: '1rem' }} />
              Déconnexion
            </Button>
          </div>
        </div>
      </div>

      {/* Overlay pour fermer les dropdowns */}
      {activeDropdown && (
        <div 
          style={{ 
            position: 'fixed', 
            inset: 0, 
            zIndex: 40 
          }} 
          onClick={() => setActiveDropdown(null)}
        />
      )}
    </header>
  )
}