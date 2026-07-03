import { HashRouter as Router, Routes, Route } from 'react-router-dom'
import { createContext } from 'react'
import Layout from './components/Layout'
import Login from './components/Login'
import Dashboard from './components/Dashboard'
import Produits from './pages/Produits'
import Clients from './pages/Clients'
import Utilisateurs from './pages/Utilisateurs'
import ProduitsPEM from './pages/ProduitsPEM'
import StratsPEM from './pages/StratsPEM'
import Commandes from './pages/Commandes'
import CommandesJuva from './pages/CommandesJuva'
import Visites from './pages/Visites'
import VisitesJuva from './pages/VisitesJuva'
import Planning from './pages/Planning'
import ValidationPlanning from './pages/ValidationPlanning'
import Prospects from './pages/Prospects'
import Prospections from './pages/Prospections'
import PlanningProspection from './pages/PlanningProspection'
import PlanningProspectionClients from './pages/PlanningProspectionClients'
import ProspectionGestionCA from './pages/ProspectionGestionCA'
import VisitesCS from './pages/VisitesCS'
import News from './pages/News'
import JoursOFF from './pages/JoursOFF'
import Traductions from './pages/Traductions'
import Profile from './pages/Profile'
import { useAuthState } from './hooks/useAuth'
import { useAuth } from './hooks/useAuthContext'

// Créer le contexte d'authentification
export const AuthContext = createContext<any>(undefined);

function AppContent() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#f8fafc'
      }}>
        <div style={{
          textAlign: 'center',
          padding: '2rem'
        }}>
          <div style={{
            width: '3rem',
            height: '3rem',
            border: '4px solid #e5e7eb',
            borderTop: '4px solid #3b82f6',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
            margin: '0 auto 1rem auto'
          }} />
          <p style={{ color: '#6b7280', fontSize: '0.875rem' }}>
            Chargement...
          </p>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Login />;
  }

  return (
    <Layout>
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="/Produits" element={<Produits />} />
        <Route path="/Magasins" element={<Clients />} />
        <Route path="/Refentiel_Utilisateurs" element={<Utilisateurs />} />
        <Route path="/Produits-PEM" element={<ProduitsPEM />} />
        <Route path="/Strats-PEM" element={<StratsPEM />} />
        <Route path="/Commandes" element={<Commandes />} />
        <Route path="/Commandesjuva" element={<CommandesJuva />} />
        <Route path="/Visites" element={<Visites />} />
        <Route path="/Visitesjuva" element={<VisitesJuva />} />
        <Route path="/Planning" element={<Planning />} />
        <Route path="/ValidationPlanning" element={<ValidationPlanning />} />
        <Route path="/Prospects" element={<Prospects />} />
        <Route path="/Prospections" element={<Prospections />} />
        <Route path="/PlanningProspection" element={<PlanningProspection />} />
        <Route path="/PlanningProspection/Clients" element={<PlanningProspectionClients />} />
        <Route path="/ProspectionGestionCA" element={<ProspectionGestionCA />} />
        <Route path="/VisitesCS" element={<VisitesCS />} />
        <Route path="/News" element={<News />} />
        <Route path="/JoursOFF" element={<JoursOFF />} />
        <Route path="/Traductions" element={<Traductions />} />
        <Route path="/Profile" element={<Profile />} />
      </Routes>
    </Layout>
  );
}

function App() {
  const authState = useAuthState();
  
  return (
    <Router>
      <AuthContext.Provider value={authState}>
        <AppContent />
      </AuthContext.Provider>
    </Router>
  )
}

export default App
