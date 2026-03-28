import './styles.css';

import { ReviewerApp } from './app/reviewerApp';

const root = document.querySelector<HTMLElement>('#app');

if (root === null) {
  throw new Error('Reviewer UI root element was not found.');
}

void new ReviewerApp(root).mount();
