describe('Login Journey', () => {
  it('should login successfully', () => {
    cy.visit('/login')
    
    cy.get('input[type="email"]').type('test@example.com')
    cy.get('input[type="password"]').type('password123')
    cy.get('button[type="submit"]').click()
    
    // Should redirect to projects page after successful login
    cy.url().should('include', '/projects')
  })

  it('should show validation errors', () => {
    cy.visit('/login')
    
    cy.get('button[type="submit"]').click()
    
    // Should show validation errors
    cy.contains('Email is required').should('be.visible')
    cy.contains('Password is required').should('be.visible')
  })
})

