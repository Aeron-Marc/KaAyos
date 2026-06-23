import { createRoot } from 'react-dom/client';
import AccountPage from './account/AccountPage';

const root = document.getElementById('account-root');
const initialData = JSON.parse(root.dataset.initial);

createRoot(root).render(<AccountPage initial={initialData} />);