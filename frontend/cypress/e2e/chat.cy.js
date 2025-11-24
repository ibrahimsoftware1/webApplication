describe('Chat Journey', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should view conversations', () => {
    cy.visit('/chat')
    
    // Should see conversations page
    cy.contains('Conversations').should('be.visible')
  })
})

